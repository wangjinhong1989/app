<?php

namespace app\api\controller;
use app\admin\model\Article;
use app\admin\model\PushList;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class SystemMessage extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=["user_id"=>$this->auth->id];

        $query=new Query();
        $data["rows"]=$query->table("fa_system_message")->alias("message")->field("*")
            ->where($where)
            ->limit($offset,$page_size)->order("id desc")->select();
        $data["count"]=$query->table("fa_system_message")->alias("message")->field("*")
            ->where($where)
            ->count();
        // 结束.

        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);

    }

}
