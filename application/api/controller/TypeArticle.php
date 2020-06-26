<?php

namespace app\api\controller;


use app\admin\model\Shoucang;
use app\admin\model\Articletype;
use app\common\controller\Api;
use think\db\Query;

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

    /**
     * 不等于类型为2快讯和7关注的
     *
     */
    public function neq_kuaixun_guanzhu()
    {
        $where=['status'=>"显示"];
        $lists=( new Query())->cache(600)->table("fa_articletype")->where($where)->order("weigh","desc")->select();
        $data=[];
        foreach ($lists as $key=>$list){
            if($list["id"]==2||$list["id"]==7){
                unset($lists[$key]);
            }else
                array_push($data,$list);
        }
        $this->success("",$data);
    }


    /**
     * 不等于类型为2快讯和7关注的
     *
     */
    public function neq_kuaixun_guanzhu1()
    {
        $where=['status'=>"显示"];
        $lists=( new Articletype())->cache(600)->where($where)->order("weigh","desc")->select();

        $this->success("",$lists);
    }

}
