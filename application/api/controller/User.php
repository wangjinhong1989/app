<?php

namespace app\api\controller;

use addons\third\library\Wechat;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login',"register_user", 'mobilelogin','mobile_register', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');

        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        // 123456 取消验证码
        if($captcha!="123456"){
            if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
                $this->error(__('Captcha is incorrect'));
            }
        }

        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, "123456", '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function register_user()
    {
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $password = $this->request->request('password');


        $user = \app\common\model\User::getByUsername($username);
        if ($user) {
            $this->error("用户已经存在了");
        }
        else{
            $ret = $this->auth->register($username, $password, '', '', ["nickname"=>$nickname]);
        }
        $this->success("成功");
    }



    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobile_register()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $password = $this->request->request('password');
        $confirm_password = $this->request->request('confirm_password');

        if (!$mobile || !$captcha||!$password||!$confirm_password) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        // 取消验证 验证码.
//        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
//            $this->error(__('Captcha is incorrect'));
//        }

        if($password!=$confirm_password&&strlen($password)>=6&&strlen($password)<=16){
            $this->error(__("密码错误"));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            $this->error(__($mobile."手机号已经注册"));
        } else {
            $ret = $this->auth->register($mobile, $password, '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code   验证码
     */
    public function register()
    {
        $username = $this->request->request('username');
        $password = $this->request->request('password');
        $email = $this->request->request('email');
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $gender = $this->request->request('gender',0);
        $birthday = $this->request->request('birthday');
        $avatar = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->gender = $gender;
        $user->birthday = $birthday;
        $user->save();
        $data = ['userinfo' => $this->auth->getUserinfo()];
        $this->success(__('修改成功'), $data);

    }

    /*
     *
     * 获取用户详细信息.
     **/
    public function detail(){

        $user_id=$this->request->request("user_id",0);
        if(!$user_id){
            $user_id=$this->auth->id;
            $data = ['userinfo' => $this->auth->getUserinfo()];

            // 是自己某人关注
            $data["is_guanzhu"]="";
        }else{

            $user= (new \app\admin\model\User())->where(["id"=>$user_id])->find();
            $data=["userinfo"=>$user];
            // 是别人查看自己是否关注了。

            $flag=(new \app\admin\model\Guanzhu())->where(["user_id"=>$this->auth->id,"follow_id"=>$user_id])->find();
            $data["is_guanzhu"]=isset($flag)?true:false;
        }


        //  获取我的粉丝数.
        $data["my_follow"]=(new \app\admin\model\Guanzhu())->where(["user_id"=>$user_id])->count();
        $data["follow_me"]=(new \app\admin\model\Guanzhu())->where(["follow_id"=>$user_id])->count();
        $data["my_article"]=(new \app\admin\model\Article())->where(["user_id"=>$user_id])->count();
        $data["my_read"]=(new \app\admin\model\ReadHistory())->where(["user_id"=>$user_id])->count();
        $data["auth_enterprise"]=(new \app\admin\model\AuthenticationEnterprise())->where(["user_id"=>$user_id])->find();
        $data["auth_media"]=(new \app\admin\model\AuthenticationMedia())->where(["user_id"=>$user_id])->find();
        $data["auth_personal"]=(new \app\admin\model\AuthenticationPersonal())->where(["user_id"=>$user_id])->find();
        $this->success(__('成功'), $data);
    }


    /**
     * 修改邮箱
     *
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @param string $email   手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        dd("2");
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        dd("1");
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {

            dd("mobile exists");
            $this->error(__('Mobile already exists'));
        }
        // 123456 取消验证码
        if($captcha!="123456") {
            $result = Sms::check($mobile, $captcha, 'changemobile');
            if (!$result) {
                $this->error(__('Captcha is incorrect'));
            }
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        dd("3");
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");

        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        if($platform=="wechat")
        $result = $app->$platform->getUserInfo(['code' => $code]);
        else if($platform=="qq"){
            $temp=[];
            $temp["access_token"] = $this->request->request("access_token");
            $temp["openid"] = $this->request->request("openid");
            $temp["refresh_token"] = $this->request->request("refresh_token");
            $temp["expires_in"] = $this->request->request("expires_in");
            dd("qq");
            dd($temp);
            $result = $app->$platform->getUserInfo1($temp);
        }
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);

            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
//                    'thirdinfo' => $result
                ];

                dd("login data");
                dd($data);
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->request("type");
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
