<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
use think\Db;
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

        $where=['article.status'=>"显示"];

        // 需要查找的类型.
        $articletype_id=$this->request->request("articletype_id",0);
        if($articletype_id){
            $where[]=["articletype.articletype_id","",$articletype_id];
        }

        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where[]=["article.title|article.content|article.content","like","%".$keyword."%"];
        }


        $data["rows"]=Db::table("fa_article as article")
            ->where($where)
            ->join("fa_articletype as articletype","articletype.id=article.articletype_id")
            ->join("fa_user as user","user.id=article.user_id")
            ->limit($offset,$page_size)->select();

        $data["page"]=$page;

        foreach ($data["rows"] as $key=>$value){
            $data["rows"][$key]["username"]=$data["rows"][$key]["user"]["username"];
            $data["rows"][$key]["avatar"]=$data["rows"][$key]["user"]["avatar"];
            $data["rows"][$key]["articletype_name"]=$data["rows"][$key]["articletype"]["name"];
            unset($data["rows"][$key]["user"]);
            unset($data["rows"][$key]["articletype"]);
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
