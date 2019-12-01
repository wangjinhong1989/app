<?php

namespace app\api\controller;

use app\admin\model\Problem;
use app\common\controller\Api;
use app\admin\model\Report;

/**
 * 首页接口
 */
class Report extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new Problem())->where(['status'=>0])->select();
        $this->success($lists);
    }
}
