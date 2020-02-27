<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\Db;
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


        $query=new Db();

        $temp="0";
        if(!empty($this->auth->id)){
            $temp=$this->auth->id;
        }
        $lists=$query::table("fa_label")->alias("label")->join("fa_mylabel mylabel","mylabel.label_id=label.id and mylabel.user_id=".$temp,"left")->where(["label.status"=>"显示"])->select("label.*,mylabel.user_id");
        $count=$query::table("fa_label")->alias("label")->where(["label.status"=>"显示"])->count();



        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
