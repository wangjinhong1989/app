<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Label extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];


        //$lists=( new \app\admin\model\Label())->where(['status'=>'显示'])->limit($offset,$page_size)->select();
        //$count=( new \app\admin\model\Label())->where(['status'=>'显示'])->count();

        $query=new Query();
        $lists=$query->table("fa_label")->alias("label")->where(["status"=>"显示"])->whereNotIn("id",function ($query){
           return $query->table("fa_mylabel")->alias("mylabel")->where("user_id",$this->auth->id)->field("label_id")->select();
        })->limit($offset,$page_size)->select()->toArray();
        $count=$query->table("fa_label")->alias("label")->where(["status"=>"显示"])->whereNotIn("id",function ($query){
            return $query->table("fa_mylabel")->alias("mylabel")->where("user_id",$this->auth->id)->field("label_id")->select()->toArray();
        })->count();

        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
