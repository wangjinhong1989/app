<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Agreement extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {

        $id=$this->request->param("id","");
        $model= new \app\admin\model\Agreement();
        $lists=$model->where(["id"=>$id])->find();
        $this->success("成功",$lists);
    }
}
