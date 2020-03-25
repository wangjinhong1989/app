<?php

namespace app\api\controller;

use addons\third\library\Wechat;
use app\admin\model\ConfigUser;
use app\admin\model\Third;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Cache;
use think\db\Query;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login',"register_user", 'mobilelogin','mobile_register', 'register', 'resetpwd', 'changeemail',  'third',"detail","bindmobile"];
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
            $this->error(__('无效参数'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('登录成功'), $data);
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
            $this->error(__('无效参数'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机错误'));
        }
        // 123456 取消验证码
        if($captcha!="123456"){
            if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
                $this->error(__('验证码错误'));
            }
        }

        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('账号锁定'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, "123456", '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('登录成功'), $data);
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
        $nickname = $username;
        $password = $this->request->request('password');


        $user = \app\common\model\User::getByUsername($username);
        if ($user) {
            $this->error("用户已经存在了");
        }
        else{
            $ret = $this->auth->register($username, $password, '', '', ["nickname"=>$nickname]);
            $this->auth->logout();
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
            $this->error(__('无效参数'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号错误'));
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
            $this->success(__('登录成功'), $data);
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
            $this->error(__('无效参数'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('邮箱错误'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('验证码错误'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('注册成功'), $data);
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
        $this->success(__('注销成功'));
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
        //$nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $gender = $this->request->request('gender',0);
        $birthday = $this->request->request('birthday');
        $avatar = $this->request->request('avatar', '');

        if(empty($avatar)){
            $this->error(__('参数为空存在空 头像未传'));
        }
        if(empty($bio)){
            $this->error(__('参数为空存在空 签名未传'));
        }
        if(empty($birthday)){
            $this->error(__('参数为空存在空 生日为空'));
        }
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('用户名已经存在'));
            }

            if($user->username!=$username){
                $my_number=Cache::get("add_article_number".$this->auth->id.date("Y",time()));
                if(empty($my_number)) $my_number=1;
                else $my_number=intval($my_number);

                $configUser=(new ConfigUser())->where([])->find();

                if(empty($configUser)){
                    return $this->error("系统未配置发文配置信息");
                }

                if($my_number<=$configUser->modify_username){
                    $user->username = $username;
                    $my_number=$my_number+1;
                    $user->nickname = $username;
                    Cache::set("add_article_number".$this->auth->id.date("Y",time()),$my_number,365*24*3600);
                }else {
                    $this->error(__('用户名一年修改'.$configUser->modify_username.'次，您已经修改过了。'));
                }

            }

        }

        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->gender = $gender;
        $user->birthday = $birthday;
        $user->save();
        $data = ['userinfo' => $this->auth->getUserinfo()];
        $this->success(__('修改成功'), $data);

    }


    /**
     * 修改会员个人信息
     *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function updateUsername()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('用户已存在'));
            }
            $user->username = $username;
        }
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

        $data["auth_value"]=(new \app\admin\model\User())->auth_status1($user_id);

        $query=new Query();
        $count=$query->table("fa_article")->alias("article")->where(["user_id"=>$user_id])->count();

        $data["my_article_count"]=$count;


        $query=new Query();
        $count=$query->table("fa_shoucang")->alias("fa_shoucang")->where(["user_id"=>$user_id])->count();

        $data["my_collect_count"]=$count;

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
            $this->error(__('有效错误'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('验证码错误'));
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
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {

            $this->error(__('手机号已经存在'));
        }
        // 123456 取消验证码
        if($captcha!="123456") {
            $result = Sms::check($mobile, $captcha, 'changemobile');
            if (!$result) {
                $this->error(__('验证码错误'));
            }
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $data = [
            'userinfo'  => $this->auth->getUserinfo(),
        ];
        $this->success("手机绑定成功",$data);
    }

    /**
     * 第三方绑定数据前，绑定手机
     *
     * @param string $email   手机号
     * @param string $captcha 验证码
     */
    public function bindmobile()
    {

        dd("参数");
        dd($this->request->request());
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $third_id = $this->request->request('third_id');
        if (!$mobile || !$captcha||!$third_id) {
            $this->error(__('无效参数'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->find()) {

            $this->error(__('手机号已经存在'));
        }

        dd("1");

        $user_info=Third::where('id', $third_id)->find();
        if (!$user_info) {

            $this->error(__('第三方绑定信息找不到'));
        }

        dd("1");

        // 123456 取消验证码
        if($captcha!="123456") {
            $result = Sms::check($mobile, $captcha, 'changemobile');
            if (!$result) {
                dd("3");
                $this->error(__('验证码错误'));
            }
        }

        dd("4");
        //  找到后。

        $user_json=\GuzzleHttp\json_decode($user_info["user_info"],true);


        $ret = $this->auth->register($user_json["username"], "123456", '', $mobile, [
            "avatar"=>$user_json["avatar"],
            "gender"=>$user_json["gender"],
            "nickname"=>$user_json["nickname"],
        ]);
        if ($ret) {

        $user=$this->auth->getUser();
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Third::update(["user_id"=>$this->auth->id],["id"=>$third_id]);
        Sms::flush($mobile, 'changemobile');
        $data = ['userinfo' => $this->auth->getUserinfo()];
            dd("5");
        $this->success(__('注册成功'), $data);
        }else{
            dd(6);
            $this->error("注册失败");
        }

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
            $this->error(__('无效参数'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        if($platform=="wechat"){
            $result = $app->$platform->getUserInfo(['code' => $code]);
        }
        else if($platform=="qq"){
            $temp=[];
            $temp["access_token"] = $this->request->request("access_token");
            $temp["openid"] = $this->request->request("openid");
            $temp["refresh_token"] = $this->request->request("refresh_token");
            $temp["expires_in"] = $this->request->request("expires_in");
            $result = $app->$platform->getUserInfo1($temp);
        }


        if ($result) {
            $loginret = \addons\third\library\Service::connect_no_register($platform, $result);

            if ($loginret=="registered") {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'third_id'  => 0,
                ];
                dd($data);
                $this->success(__('登录成功'),$data);
            }
            if ($loginret>0) {
                $data = [
                    'userinfo'  => null,
                    'third_id'  => $loginret,
                ];
                dd($data);
                $this->success(__('去绑定手机号'),$data);
            }
        }
        $this->error(__('注册失败'), $url);
    }


    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third_bind()
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
        if($platform=="wechat"){
            $result = $app->$platform->getUserInfo(['code' => $code]);
        }
        else if($platform=="qq"){
            $temp=[];
            $temp["access_token"] = $this->request->request("access_token");
            $temp["openid"] = $this->request->request("openid");
            $temp["refresh_token"] = $this->request->request("refresh_token");
            $temp["expires_in"] = $this->request->request("expires_in");
            $result = $app->$platform->getUserInfo1($temp);
        }
        if ($result) {
            $loginret = \addons\third\library\Service::bind($platform, $result,[],0,$this->auth->id);

            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                ];
                $this->success(__('绑定成功'),$data);
            }
        }
        $this->error(__('绑定失败'), $url);
    }


    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third_unbind()
    {
        $platform = $this->request->request("platform");
        (new Third())->where(["platform"=>$platform,"user_id"=>$this->auth->id])->delete();
        $this->success("解绑");
    }

    /**
     * 查看是否绑定
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third_detail()
    {
//        $platform = $this->request->request("platform");
        $data1=[];

        $user = $this->auth->getUser();
        $data=(new Query())->table("fa_third")->where(["platform"=>"qq","user_id"=>$user->id])->field("platform,openname")->find();
        if($data){

            $data1["qq"]=$data["openname"];
        }else {
            $data1["qq"]="未绑定";
        }
        $data=(new Query())->table("fa_third")->where(["platform"=>"wechat","user_id"=>$user->id])->field("platform,openname")->find();
        if($data){

            $data1["wechat"]=$data["openname"];
        }else{
            $data1["wechat"]="未绑定";
        }

        $this->success("",$data1);
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
            $this->error(__('无效参数'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('用户找不到'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('验证码错误'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('邮箱错误'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('用户找不到'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('验证码错误'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('重置成功'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
