<?php

namespace addons\third\library;

use addons\third\model\Third;
use app\common\model\User;
use fast\Random;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * 第三方登录服务类
 *
 * @author Karson
 */
class Service
{

    /**
     * 第三方登录
     * @param string $platform 平台
     * @param array  $params   参数
     * @param array  $extend   会员扩展信息
     * @param int    $keeptime 有效时长
     * @return boolean
     */
    public static function connect($platform, $params = [], $extend = [], $keeptime = 0)
    {
        $time = time();
        $values = [
            'platform'      => $platform,
            'openid'        => $params['openid'],
            'openname'      => isset($params['userinfo']['nickname']) ? $params['userinfo']['nickname'] : '',
            'access_token'  => $params['access_token'],
            'refresh_token' => $params['refresh_token'],
            'expires_in'    => $params['expires_in'],
            'logintime'     => $time,
            'expiretime'    => $time + $params['expires_in'],
        ];
        $auth = \app\common\library\Auth::instance();

        $auth->keeptime($keeptime);
        $third = Third::get(['platform' => $platform, 'openid' => $params['openid']]);
        if ($third) {
            $user = User::get($third['user_id']);
            if (!$user) {
                return false;
            }
            $third->save($values);
            return $auth->direct($user->id);
        } else {
            // 先随机一个用户名,随后再变更为u+数字id
            $username = Random::alnum(20);
//            $password = Random::alnum(6);
            $password = "123456";
            $domain = request()->host();


            // 检测用户名或邮箱、手机号是否存在

            Db::startTrans();
            try {
                // 默认注册一个会员
                $result = $auth->register($username, $password, $username . '@' . $domain, '', $extend, $keeptime);
                if (!$result) {
                    return false;
                }
                $user = $auth->getUser();
                $fields = ['username' => 'u' . $user->id, 'email' => 'u' . $user->id . '@' . $domain];
                if (isset($params['userinfo']['nickname'])) {
                    $fields['nickname'] = $params['userinfo']['nickname'];
                    if (User::getByUsername($fields['nickname'])) {
                        $fields['username']=$fields['nickname'].rand(1000,9999);
                        $fields['nickname']=$fields['username'];
                    }else
                        $fields['username']=$fields['nickname'];

                }
                if (isset($params['userinfo']['avatar'])) {
                    $fields['avatar'] = (($params['userinfo']['avatar']));
                }

                if (isset($params['userinfo']['gender'])) {
                    $fields['gender'] =  $params['userinfo']['gender'];
                }
                // 更新会员资料
                $user = User::get($user->id);
                $user->save($fields);

                // 保存第三方信息
                $values['user_id'] = $user->id;
                Third::create($values);
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $auth->logout();
                return false;
            }

            // 写入登录Cookies和Token
            return $auth->direct($user->id);
        }
    }


    /**
     * 第三方登录, 如果未绑定，则不注册，返回记录信息。
     * @param string $platform 平台
     * @param array  $params   参数
     * @param array  $extend   会员扩展信息
     * @param int    $keeptime 有效时长
     * @return boolean
     */
    public static function connect_no_register($platform, $params = [], $extend = [], $keeptime = 0)
    {
        $time = time();
        $values = [
            'platform'      => $platform,
            'openid'        => $params['openid'],
            'openname'      => isset($params['userinfo']['nickname']) ? $params['userinfo']['nickname'] : '',
            'access_token'  => $params['access_token'],
            'refresh_token' => $params['refresh_token'],
            'expires_in'    => $params['expires_in'],
            'logintime'     => $time,
            'expiretime'    => $time + $params['expires_in'],
        ];
        $auth = \app\common\library\Auth::instance();

        $auth->keeptime($keeptime);
        $third = Third::get(['platform' => $platform, 'openid' => $params['openid']]);
        if ($third&&$third["user_id"]>0) {
            $user = User::get($third['user_id']);
            if (!$user) {
                return false;
            }
            $third->save($values);

            $auth->direct($user->id);

            return "registered";
        } else {

            Db::startTrans();
            try {
                $fields = [];
                if (isset($params['userinfo']['nickname'])) {
                    $fields['nickname'] = $params['userinfo']['nickname'];
                    if (User::getByUsername($fields['nickname'])) {
                        $fields['username']=$fields['nickname'].rand(1000,9999);
                        $fields['nickname']=$fields['username'];
                    }else
                        $fields['username']=$fields['nickname'];

                }
                if (isset($params['userinfo']['avatar'])) {
                    $fields['avatar'] = (($params['userinfo']['avatar']));
                }
                if (isset($params['userinfo']['gender'])) {
                    $fields['gender'] =  $params['userinfo']['gender'];
                }

                // 保存第三方信息
                $values['user_id'] = 0;
                $values["user_info"]=json_encode($fields);
                if($third){
                    $third->save($values);
                }else
                    $third=Third::create($values);

                Db::commit();

                return $third->id;

            } catch (Exception $e) {
                Db::rollback();
                $auth->logout();
                return false;
            }

        }
    }



    public static function bind($platform, $params = [], $extend = [], $keeptime = 0,$user_id)
    {
        $time = time();
        $values = [
            'platform'      => $platform,
            'openid'        => $params['openid'],
            'openname'      => isset($params['userinfo']['nickname']) ? $params['userinfo']['nickname'] : '',
            'access_token'  => $params['access_token'],
            'refresh_token' => $params['refresh_token'],
            'expires_in'    => $params['expires_in'],
            'logintime'     => $time,
            'expiretime'    => $time + $params['expires_in'],
        ];
        $auth = \app\common\library\Auth::instance();

        $third = Third::get(['platform' => $platform, 'openid' => $params['openid']]);
        $auth->keeptime($keeptime);
        dd("third");
        dd($third);
        Db::startTrans();
        try {
            if ($third) {
                $values['user_id'] = $user_id;
                $third->save($values);
                Db::commit();
                return true;
            }


        } catch (PDOException $e) {
            Db::rollback();
            return false;
        }

        dd("third2");


        $values['user_id'] = $user_id;
        $back=Third::create($values);
        dd($back);
        if($back)
        return true;
        else
            return false;


    }

    public static function unbind($user_id)
    {
        $auth = \app\common\library\Auth::instance();
        Db::startTrans();
        try {

            (new \app\admin\model\Third())->where(["user_id"=>$user_id])->delete();
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            return false;
        }

        // 写入登录Cookies和Token
        return $auth->direct($user_id);

    }


}
