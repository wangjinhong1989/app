<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Version extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $model= new \app\admin\model\Version();
        $lists=$model->where(["status"=>"是"])->order("version_code","desc")->limit(0,1)->find();
        $this->success("成功",$lists);
    }
}
