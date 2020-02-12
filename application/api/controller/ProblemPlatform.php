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

        $data["rows"]=(new Problem())->where(['status'=>'显示'])->limit($offset,$page_size)->select();
        $data["count"]=(new Problem())->where(['status'=>'显示'])->count();

        $data["page"]=$page;

        foreach ($data["rows"] as $k=>$l){
            $data["rows"][$k]["time"]=date("Y-m-d H:i:s",$l["time"]);
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

    public function detail()
    {
        $id=$this->request->request("id",0);
        $lists=(new Problem())->where(['id'=>$id])->find();
        $lists["time"]=date("Y-m-d H:i:s",$lists["time"]);
        $this->success("成功",$lists);
    }


}
