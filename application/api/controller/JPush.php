<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\PushList;
use app\admin\model\PushType;
use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\db\Query;
use think\Hook;
use think\Config;

/**
 * 手机短信接口
 */
class JPush extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     */
    public function send()
    {


        $client =   new \JPush\Client( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"));

        $type=$this->request->request("type",0);
        $article= (new Article())->where(["id"=>["gt",0],"articletype_id"=>$type])->find();
        $data=[
            "type"=>$type,
            "data"=>$article
        ];

        try {
            $back=$client->push()
                ->setPlatform('all')
                ->addAllAudience()
//                ->setMessage("这是标题","标题","快讯",["672"])
                    ->setMessage(\GuzzleHttp\json_encode($data))
                ->setNotificationAlert("您有个新回复")
                ->send();

            return  $this->success("",$back);
        } catch (\JPush\Exceptions\JPushException $e) {
            // try something else here
            print $e;
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


    public function push_list()
    {

        $query = new Query();

        $query->table("fa_push_list")->chunk(100, function ($list) {

            // 需要推送的列表.
            foreach ($list as  $l){
                $this->push_data($l);

                //$model= new PushList();
                //$model->where(["id"=>$l["id"]])->delete();
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
            $article=\GuzzleHttp\json_decode($value["content"]);
            $userList=(new Query())->table("fa_guanzhu")->alias("guanzhu")->where(["follow_id"=>$article["user_id"]])->select();
            foreach ($userList as  $user){
                $this->push_method($data,$type_data,$user["id"]);
            }

        }


    }

    public function push_method($data,$type_data,$user_id){
        $user=(new User())->where(["id"=>$user_id])->find();

        $push_config=new \app\admin\model\PushConfig();
        $config=$push_config->where(["user_id"=>$user_id])->find();

        $temp=\GuzzleHttp\json_decode($data["content"]);

        if(!empty($config)){

            if($config["is_accept_notify"]=="否"){
                return "";
            }

            if($config["is_article_notify"]=="否"){
                if(!empty($temp["articletype_id"])&&$temp["articletype_id"]>0&&$temp["articletype_id"]!=2){
                    return "";
                }
            }

            if($config["is_kuaixun_notify"]=="否"){
                if(!empty($temp["articletype_id"])&&$temp["articletype_id"]>0&&$temp["articletype_id"]==2){
                    return "";
                }
            }

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
        }


        $client =   new \JPush\Client( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"));
        // 解析需要推送的数据.
        try {
            $back=$client->push()
                ->setPlatform('all')
                ->addAlias($user["id".$user["username"]])
                ->setMessage(\GuzzleHttp\json_encode($data))
                ->setNotificationAlert("您有个新".$type_data["type"])
                ->send();
            return  $this->success("",$back);
        } catch (\JPush\Exceptions\JPushException $e) {
            print $e;
        }
    }

}
