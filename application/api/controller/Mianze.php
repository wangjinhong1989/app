<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\db\Query;
use think\Session;

/**
 * 首页接口
 */
class Mianze extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    /**
     * 首页
     *
     */
    public function wenzhang()
    {

        $model= new Query();
        $lists= $model->table("fa_mianze1")->where([])->select();
       return $this->success("成功",$lists);
    }


    /**
     * 首页
     *
     */
    public function tuijian()
    {

        $model= new Query();
        $lists= $model->table("fa_mianze2")->where([])->select();
        return $this->success("成功",$lists);
    }



    /**
     * 首页
     *
     */
    public function kuaixun()
    {

        $model= new Query();
        $lists= $model->table("fa_mianze3")->where([])->select();
        return $this->success("成功",$lists);
    }

}
