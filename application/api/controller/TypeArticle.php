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
        $show_page=$this->request->request("show_page","");

        $where=['status'=>"显示"];
        $whereExp=" 1 ";
        if(!empty($show_page)){

            $whereExp='find_in_set("'.$show_page.'",show_page)';

        }
        $lists=( new Articletype())->where($where)->whereExp('',$whereExp)->order("weigh","desc")->select();
        $this->success("",$lists);
    }

}
