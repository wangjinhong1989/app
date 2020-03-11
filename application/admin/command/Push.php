<?php

namespace app\admin\command;

use app\admin\command\Api\library\Builder;
use app\admin\model\SystemMessage;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;


use app\admin\model\Article;
use app\admin\model\PushList;
use app\admin\model\PushType;
use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\db\Query;
use think\Hook;

class Push extends Command
{
    protected function configure()
    {
        $site = Config::get('site');
        $this
            ->setName('push')
            ->addOption('push', 'p', Option::VALUE_OPTIONAL, 'default push url', '')
            ->addOption('all', 'a', Option::VALUE_OPTIONAL, 'default push url', '')
            ->setDescription('J Push');
    }

    protected function execute(Input $input, Output $output)
    {

        $push=$input->getOption("push");
        if($push){

            $this->push_list();
            dd("test");
        }

        $all=$input->getOption("all");
        if($all){
            $this->test();
        }
    }


    // 发送推送消息
    public function send_push(){


        $query= new Query();

        $query->table("fa_user")->chunk(100,function ($user){

            foreach ($user as $u){

                $typeModel=new PushType();

                $client =   new \JPush\Client( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"));

                $type=$this->request->request("type",0);

                $type_data=$typeModel->where(["id"=>$type])->find();
                $article= (new Article())->where(["id"=>["gt",0]])->find();
                $data=[
                    "type"=>$type_data["id"],
                    "data"=>$article
                ];
                try {
                    $back=$client->push()
                        ->setPlatform('all')
                        ->addAlias($u["id"].$u["username"])
                        ->setMessage(\GuzzleHttp\json_encode($data))
                        ->setNotificationAlert("您有个新".$type_data["type"])
                        ->send();

                    return  $this->success("",$back);
                } catch (\JPush\Exceptions\JPushException $e) {
                    print $e;
                }

            }

        });





    }
    // 发送推送消息
    public function test(){
        $client =   new \JPush\Client( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"));

        $data=[
            "type"=>"2",
            "data"=>"123"
        ];
        try {
            $back=$client->push()
                ->setPlatform('all')
                ->addAlias("1011218380461887")
//                ->message("test",["title"=>"title","content_type"=>"2","extras"=>\GuzzleHttp\json_encode($data)])
                ->iosNotification("new test message",[
                'extras' => $data
                ])->addAndroidNotification("new test message","title",null,["extras"=>$data])

                ->send();


            dd($back);
        } catch (\JPush\Exceptions\JPushException $e) {
            print $e;
        }





    }



    public function push_list()
    {
        set_time_limit(0);

        $query = new Query();

        $query->table("fa_push_list")->where(["status"=>0])->chunk(100, function ($list) {

            // 需要推送的列表.
            foreach ($list as  $l){
                $this->push_data($l);

                $model= new PushList();
                $push=$model->where(["id"=>$l["id"]])->find();
                $push->status=1;
                $push->save();

                dd($l["id"]);
            }
        });
    }


    public function push_data($value){

        $typeModel=new PushType();
        $type_data=$typeModel->where(["id"=>$value["push_type_id"]])->find();
        $data=[
            "type"=>$value["push_type_id"],
            "data"=>$value["content"]
        ];

        // 关注我的，通知我有更新
        if($data["type"]===7){
            $article=\GuzzleHttp\json_decode($value["content"],true);
            $userList=(new Query())->table("fa_guanzhu")->alias("guanzhu")->where(["follow_id"=>$article["user_id"]])->select();
            foreach ($userList as  $user){
                $this->push_method($data,$type_data,$user["user_id"]);
            }

        }else if($data["type"]===1){
            // 评论
            $content=\GuzzleHttp\json_decode($value["content"],true);
            $article=(new Query())->table("fa_article")->where(["id"=>$content["article_id"]])->find();
            $this->push_method($data,$type_data,$article["user_id"]);
        }
        else if($data["type"]===3){
            // 回复
            $content=\GuzzleHttp\json_decode($value["content"],true);
            $this->push_method($data,$type_data,$content["user_id"]);
        }
        else if($data["type"]===2){
            // 回复
            $content=\GuzzleHttp\json_decode($value["content"],true);
            dd($type_data);
            $this->push_method($data,$type_data,$content["follow_id"]);
        }
        else if($data["type"]===4){
            // 回复
            $content=\GuzzleHttp\json_decode($value["content"],true);
            $reply= (new Query())->table("fa_reply")->where(["id"=>$content["at_id"]])->find();
            $this->push_method($data,$type_data,$reply["user_id"]);
        }
        else if($data["type"]===5){
            // 系统推送消息
            $this->push_method($data,$type_data,$value["user_id"]);
        }


    }

    public function push_method($data,$type_data,$user_id){
        $user=(new User())->where(["id"=>$user_id])->find();

//        dd($user_id);
//        dd($user);
        $push_config=new \app\admin\model\PushConfig();
        $config=$push_config->where(["user_id"=>$user_id])->find();

        $temp=\GuzzleHttp\json_decode($data["data"],true);

        dd("1111");
        if(!empty($config)){
            dd("3333");
            if($config["is_accept_notify"]=="否"){
                return "";
            }
            dd("444");
            if($config["is_article_notify"]=="否"){
                if(!empty($temp["articletype_id"])&&$temp["articletype_id"]>0&&$temp["articletype_id"]!=2){
                    return "";
                }
            }
            dd("555");
            if($config["is_kuaixun_notify"]=="否"){
                if(!empty($temp["articletype_id"])&&$temp["articletype_id"]>0&&$temp["articletype_id"]==2){
                    return "";
                }
            }
            dd("6666");
            if($config["is_follow_notify"]=="否"){
                return "";
            }else{
                // 查询是否关注了.
                // 并且消息是文章.
                if(!empty($temp["articletype_id"])){
                    $guanzhu=(new \app\admin\model\Guanzhu())->where(["user_id"=>$user_id,"follow_id"=>$temp["user_id"]])->find();
                    if(!$guanzhu){
                        return "";
                    }
                }
            }
            dd("2222");
        }
        dd("777");

        $client =   new \JPush\Client( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"));

        $msg="您有新通知";


        if($type_data["id"]==7){
            $msg="您关注的人发布新文章了";
        }else if($type_data["id"]==1){
            $msg="您的文章有新评论了";
        }
        else if($type_data["id"]==2){
            $msg="有好友关注了您";
        }else if($type_data["id"]==3){
            $msg="您有新回复";
        }else if($type_data["id"]==4){
            $msg="您有新点赞";
        }else if($type_data["id"]==5){
            $msg="系统公告请查看";
        }
        $data1=$data;
        $data1["data"]=\GuzzleHttp\json_decode($data["data"],true);
        if($type_data["id"]==7){
            $content=\GuzzleHttp\json_decode($data["data"],true);
            $data1["data"]=$content["id"];
        }else
            $data1["data"]="";
        // 解析需要推送的数据.
        try {
            $back=$client->push()
                ->setPlatform(['ios', 'android'])
                ->addAlias($user["id"].$user["username"])
                ->iosNotification($msg,['extras' => $data])
                ->addAndroidNotification($msg,$msg,null,$data1)
                ->send();
            dd("88888");
            $model=new SystemMessage();
            $model->create([
                "user_id"=>$user["id"],
                "status"=>"未读",
                "time"=>time(),
                "content"=>$msg
            ]);
        } catch (\JPush\Exceptions\JPushException $e) {
            print $e;
        }
    }

}
