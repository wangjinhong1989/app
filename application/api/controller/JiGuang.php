<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\Hook;
use think\Config;

/**
 * 手机短信接口
 */
class JiGuang extends Api
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
        $mobile = $this->request->request("mobile");

        $client =  new \JiGuang\JSMS( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"), [ 'disable_ssl' => true ]);

        echo \GuzzleHttp\json_encode($client->sendCode($mobile, 1,12236));
    }

    /**
     * 检测验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {

        $code = $this->request->request("code");

        $client =  new \JiGuang\JSMS(config("jiguang_app_key"), config("jiguang_master_secret"), [ 'disable_ssl' => true ]);

        $this->success("",$client->checkCode(1, $code));

    }
}
