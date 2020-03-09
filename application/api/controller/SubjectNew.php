<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\db\Query;
use think\view\driver\Think;

/**
 * 首页接口
 */
class SubjectNew extends Api
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
        $sort=$this->request->request("sort",'paixu');
        $order=$this->request->request("order",'asc');
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["article.status"]=["eq","显示"];


        $query=new Query();
        $data["rows"]=$query->table("fa_subject_new")->alias("subject")->where($where)
            ->field("article.*")
            ->join("fa_article article","article.id=subject.article_id","left")
            ->order($sort ,$order) ->limit($offset,$page_size)->select();


        $data["count"]=$query->table("fa_subject_new")->alias("subject")->join("fa_article article","article.id=subject.article_id","left")->where($where)->count();


        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
