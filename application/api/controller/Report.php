<?php

namespace app\api\controller;

use app\admin\model\Jubao;
use app\common\controller\Api;

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
        $lists=(new Jubao())->where(['status'=>0])->select();
        $this->success($lists);
    }

    public function add()
    {

        $model=new Jubao();
        $this->request;
        $model->save($this->request->param());
        $this->success($this->request->param());

    }
}