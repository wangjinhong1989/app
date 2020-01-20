<?php

namespace app\api\controller;


use app\admin\model\Shoucang;
use app\admin\model\Articletype;
use app\common\controller\Api;
/**
 * 首页接口
 */
class TypeArticle extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=( new Articletype())->where(['status'=>"显示"])->order("weigh","desc")->select();
        $this->success("",$lists);
    }

}
