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
class Daodu extends Api
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
        $lists=$model->table("fa_daodu")->where(['status'=>'显示'])->orderRaw("rand()")->limit(0,1)->select();
        $this->success("成功",$lists);
    }
}
