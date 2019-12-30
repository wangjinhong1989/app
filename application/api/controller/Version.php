<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Version extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {

        $old_version=$this->request->param("oldversion","");
        $model= new \app\admin\model\Version();
        $lists=$model->where(["oldversion"=>$old_version])->find();
        $this->success("成功",$lists);
    }
}
