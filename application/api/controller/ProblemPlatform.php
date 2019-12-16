<?php

namespace app\api\controller;


use app\common\controller\Api;
use app\admin\model\Problem;
/**
 * 首页接口
 */
class ProblemPlatform extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new Problem())->where(['status'=>'显示'])->select();
        $this->success($lists);
    }


}
