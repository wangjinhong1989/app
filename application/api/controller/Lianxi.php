<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\db\Query;

/**
 * 首页接口
 */
class Lianxi extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $model=    new Query();
        $lists=$model->table("fa_lianxi")->where([])->order("paixu","asc")->select();
        $this->success("成功",$lists);
    }
}
