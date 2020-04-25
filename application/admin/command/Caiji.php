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
            ->setDescription('cai ji');
    }

    protected function execute(Input $input, Output $output)
    {

        set_time_limit(0);

        $type=$input->getOption("type");
        switch ($type){
            case "kuaixun":$this->kuaixun();break;
            default : break;
        }



    }

    protected function kuaixun(){
        $httpParams = array(
            'access_key' => $this->key,
            'date' => time()
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

        //var_dump($json);

        foreach ($json as $j){

            \app\admin\model\Caiji::create(
                [
                    "type"=>"快讯",
                    "contentjson"=>json_encode($j),
                    "status"=>"写入",
                    "create_time"=>date("Y-m-d H:i:s",time())
                ]
            );

        }
    }

}
