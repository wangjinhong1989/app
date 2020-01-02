<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Data extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //  添加用户
    public function  user(){

        for ($i=100;$i<200;$i++){
            $mobile="13800000".$i;
            $ret = $this->auth->register("username_".$i, "123456", '', $mobile, []);
        }
    }
}
