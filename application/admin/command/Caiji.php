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

class Caiji extends Command
{
    protected $key="138a1dd40b2ed4cc67a0de8031ff94f8";
    protected $secret_key="871af1b8b014f64a";
    protected function configure()
    {
        $this
            ->setName('caiji')
            ->addOption('type', 't', Option::VALUE_OPTIONAL, 'default caiji url', '')
            ->addOption('set', 's', Option::VALUE_OPTIONAL, 'default set ids', '')
            ->addOption('begin', 'b', Option::VALUE_OPTIONAL, 'default set article ids', '')
            ->setDescription('cai ji');
    }

    protected function execute(Input $input, Output $output)
    {

        set_time_limit(0);

        $type=$input->getOption("type");
        $set=$input->getOption("set");
        $begin=$input->getOption("begin");
        if($set){
            $set=intval($set);
            file_put_contents("ids.txt",$set);
        }
        if($begin){
            $begin=intval($begin);
            file_put_contents("begin.txt",$begin);
        }
        switch ($type){
            case "kuaixun":$this->kuaixun();break;
            case "article":$this->article();break;
            default : break;
        }



    }

    protected function kuaixun(){

        $ids=file_get_contents("ids.txt");
        if(!$ids){
            echo "input set ids  error \r\n";die;
        }
        $ids=intval($ids);
        if(!is_numeric($ids)){

            echo "ids error \r\n";die;
        }

        $httpParams = array(
            'access_key' => $this->key,
            'date' => time(),
            "id"=>$ids,
            "flag"=>"up",
            "limit"=>1
        );

        $signParams = array_merge($httpParams, array('secret_key' => $this->secret_key));

        ksort($signParams);
        $signString = http_build_query($signParams);

        $httpParams['sign'] = strtolower(md5($signString));

        $url = 'http://api.coindog.com/live/list?'.http_build_query($httpParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,20);
        $curlRes = curl_exec($ch);

        var_dump($curlRes);
        curl_close($ch);
        $json = json_decode($curlRes, true);

        var_dump($json);

        die;
        foreach ($json["list"] as $j){

            if($json["top_id"]!=$ids){

                foreach ($j["lives"] as $v){


                    \app\admin\model\Article::create(
                        [
                            "title"=>str_replace("【","",mb_strstr($v["content"],"】",true).""),
                            "content"=>str_replace("】","",mb_strstr($v["content"],"】",false).""),
                            "lk_count"=>$v["down_counts"],
                            "lh_count"=>$v["up_counts"],
                            "articletype_id"=>2,
                            "is_recommendation"=>"否",
                            "status"=>"显示",
                            "create_time"=>date("Y-m-d H:i:s",time())
                        ]
                    );
                    var_dump($v);
                }


                file_put_contents("ids.txt",$json["top_id"]);
            }


        }
    }


    protected function article(){

        $ids=file_get_contents("begin.txt");
        if(!$ids){
            echo "input set begin ids  error \r\n";die;
        }
        $ids=intval($ids);
        if(!is_numeric($ids)){

            echo "begin ids error \r\n";die;
        }


        $httpParams = array(
            'access_key' => $this->key,
            'date' => time(),
            'last_id' => $ids,
        );

        $signParams = array_merge($httpParams, array('secret_key' => $this->secret_key));

        ksort($signParams);
        $signString = http_build_query($signParams);

        $httpParams['sign'] = strtolower(md5($signString));

        $url = 'http://api.coindog.com/topic/list?'.http_build_query($httpParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlRes = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($curlRes, true);


        var_dump($json);

        foreach ($json as $k=>$j){

            if($k==0&&$j["id"]!=$ids){
                file_put_contents("begin.txt",$j["id"]);
            }

            if(Config::get("site.采集开关")==0){
                echo "采集到了，不入库";
                continue;
            }
            if(Config::get("site.采集开关")==1){
                echo "采集到了，入库";

            }

                    $bak=\app\admin\model\Article::create(
                        [
                            "title"=>$j["title"],
                            "content"=>preg_replace('/<a .*?href="(.*?)".*?>金色财经/is',"<a> 金色财经",$j["content"]),
                            "img"=>$j["thumbnail"],
                            "description"=>$j["summary"],
                            "user_id"=>Config::get("site.采集发文用户ID"),
                            "articletype_id"=>1,
                            "is_recommendation"=>"否",
                            "weigh"=>0,
                            "status"=>"显示",
                            "create_time"=>date("Y-m-d H:i:s",time())
                        ]
                    );

            $bak->weigh=$bak->id;
            $bak->save();
            var_dump($bak->weigh);
            var_dump($bak->id);
//            $model= new \app\admin\model\Article;
//            $id=$model->getLastInsID();
//            var_dump($id);
//            $model->u
            }

    }


}
