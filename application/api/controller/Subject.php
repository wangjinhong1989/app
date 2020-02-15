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
        $sort=$this->request->request("sort",'weigh');
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
            ->order($sort ,$order) ->limit($offset,$page_size)->select();


        $data["count"]=$query->table("fa_subject")->alias("subject")->where($where)->count();


        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }


    /**
     * 详情页
     *
     */
    public function detail()
    {
        $id=$this->request->request("id",0);
        $model=new \app\admin\model\Subject();
        $where=["status"=>"显示"];

        $detail=$model->where($where)->limit(0,1)->find();
        if($detail) {
            $detail->read_count = $detail->read_count + 1;
            $detail->save();

        }

        $this->success("成功",$detail);
    }


}
