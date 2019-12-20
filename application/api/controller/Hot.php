<?php

namespace app\api\controller;


use app\admin\model\HotSearch;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Hot extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {
        $lists=( new HotSearch())->where(['status'=>0])->select();
        $this->success("成功",$lists);
    }
}
