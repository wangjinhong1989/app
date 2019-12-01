<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;

/**
 * 首页接口
 */
class Ad extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new Guanggao())->where(['status'=>0])->select();
        $this->success($lists);
    }
}
