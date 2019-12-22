<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
use think\Db;
use think\db\Query;
use think\view\driver\Think;

/**
 * 首页接口
 */
class ArticleManager extends Api
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
        $offset=($page-1)*$page_size;
        $model=new Article();
        $data=[];
//        $data["rows"]=$model->with("user,articletype")->where(['article.status'=>"显示"])->limit($offset,$page_size)->select();
//        $data["count"]=$model->where(['status'=>0])->count();

        $where=[];
        $where["article.status"]=["eq","显示"];

        // 需要查找的类型.
        $articletype_id=$this->request->request("articletype_id",0);
        if($articletype_id){
            $where["article.articletype_id"]=["eq",$articletype_id];
        }

        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["article.title|article.description|article.content"]=["like","%".$keyword."%"];
        }


        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->count();



        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
