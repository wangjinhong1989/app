<?php

namespace app\api\controller;


use app\common\controller\Api;
use app\admin\model\Problem;
/**
 * 首页接口
 */
class HotSearch extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new app\admin\model\HotSearch())->where(['status'=>0])->select();
        $this->success($lists);
    }
}
