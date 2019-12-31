<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\db\Query;
use think\view\driver\Think;

/**
 * 首页接口
 */
class Subject extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $sort=$this->request->request("sort",'sort');
        $order=$this->request->request("order",'asc');
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["status"]=["eq","显示"];

        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["title|description|content"]=["like","%".$keyword."%"];
        }

        // 单独查询.
        $title=$this->request->request("title","");
        if($title){
            $where["title"]=["like","%".$title."%"];
        }
        $description=$this->request->request("description","");
        if($description){
            $where["description"]=["like","%".$description."%"];
        }
        $content=$this->request->request("content","");
        if($content){
            $where["content"]=["like","%".$content."%"];
        }

        $query=new Query();
        $data["rows"]=$query->table("fa_subject")->alias("subject")->where($where)
            ->limit($offset,$page_size)->select();


        $data["count"]=$query->table("subject")->alias("subject")->where($where)->count();


        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



}
