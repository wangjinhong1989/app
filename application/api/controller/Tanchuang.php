<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Tanchuang extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    /**
     * 首页
     *
     */
    public function get_one()
    {
        $where=[];
        $where["status"]=["eq","显示"];
        $where["begin_time"]=["elt",time()];
        $where["end_time"]=["egt",time()];

        $model=(new \app\admin\model\Tanchuang());
        $lists=(new \app\admin\model\Tanchuang())->where($where)->find();
        $lists->image="http://app.biyouliao8.com".$lists->image;
        dd($model->getLastSql());
        $this->success("成功",$lists);
    }


}
