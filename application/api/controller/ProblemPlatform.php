<?php

namespace app\api\controller;


use app\common\controller\Api;
use app\admin\model\Problem;
use think\Request;

/**
 * 首页接口
 */
class ProblemPlatform extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        $data=[];

        $where=[];
        $where["status"]=["eq","显示"];

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["reason"]=["like","%".$keyword."%"];
        }

        $data["rows"]=(new Problem())->where($where)->limit($offset,$page_size)->order("time","desc")->select();
        $data["count"]=(new Problem())->where($where)->count();

        $data["page"]=$page;
        $data["rows"]=collection($data["rows"])->toArray();

        foreach ($data["rows"] as $key=>&$row){
            $row["date"]=date("Y-m-d",$row["time"]);
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

    public function detail()
    {
        $id=$this->request->request("id",0);
        $lists=(new Problem())->where(['id'=>$id])->find();
        $this->success("成功",$lists);
    }


}
