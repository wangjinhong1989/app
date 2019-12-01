<?php

namespace app\api\controller;


use app\admin\validate\HotSearch;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Hot extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=( new HotSearch())->where(['status'=>0])->select();
        $this->success($lists);
    }
}
