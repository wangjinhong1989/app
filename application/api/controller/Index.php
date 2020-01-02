<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function test()
    {
        $this->sendTemplateSMS("17380613281",['1234','5678'],1);

    }

    function sendTemplateSMS($to, $datas, $tempId)
    {
        //主帐号
        $accountSid = '8aaf07086f17620f016f319cf6d61120';

        //主帐号Token
        $accountToken = '32ea281adbb94320ad758a2b1c061ecb';

        //应用Id
        $appId = '8aaf07086f17620f016f319cf73d1126';

        //请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';

        //请求端口
        $serverPort = '8883';

        //REST版本号
        $softVersion = '2013-12-26';
        $rest = new \app\common\library\REST($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);

        // 发送模板短信
        echo "Sending TemplateSMS to $to <br/>";
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);

        var_dump($result);
        if ($result == NULL) {
            echo "result error!";
            return "";
        }
        if ($result->statusCode != 0) {
            echo "error code :" . $result->statusCode . "<br>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        } else {
            echo "Sendind TemplateSMS success!<br/>";
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            echo "dateCreated:" . $smsmessage->dateCreated . "<br/>";
            echo "smsMessageSid:" . $smsmessage->smsMessageSid . "<br/>";
            //TODO 添加成功处理逻辑
        }

    }
}
