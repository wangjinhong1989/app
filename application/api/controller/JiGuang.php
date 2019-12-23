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

        return $this->success("","123456789");die;

        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }

        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $mobile = $this->request->request("mobile");

        $client =  new \JiGuang\JSMS( Config::get("jiguang_app_key"),  Config::get("jiguang_master_secret"), [ 'disable_ssl' => true ]);

        $res=$client->sendCode($mobile, 1,12236);

        if($res["http_code"]==200){
            $this->success();
        }else{
            $this->error("短信错误",$res,1001);
        }
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


        return $this->success();die;

        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }


        $code = $this->request->request("code");
        $msg_id = $this->request->request("msg_id");

        $client =  new \JiGuang\JSMS(config("jiguang_app_key"), config("jiguang_master_secret"), [ 'disable_ssl' => true ]);

        $res=$this->success("",$client->checkCode($msg_id, $code));
        if($res["http_code"]==200){
            $this->success();
        }else{
            $this->error("认证失败",$res,1001);
        }

    }
}
