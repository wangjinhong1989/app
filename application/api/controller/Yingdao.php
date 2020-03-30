<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Yingdao extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $time=time();
        $lists=(new \app\admin\model\Yingdao())->where([])->find();



        $this->success("成功",$lists);

    }
}
