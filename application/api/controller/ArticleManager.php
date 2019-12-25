<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Guanggao;
use app\admin\model\ReadHistory;
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


        // 查询某个人的文章。
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }


        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->order("article.id desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->count();


        $guanggao=new Guanggao();

        $ad=Db::table($guanggao->getTable())->where([])->limit(1)->select();

        foreach ($data["rows"] as $key=>$value){
            $data["rows"][$key]["is_ad"]=false;
        }

        if(!empty($ad)){

            $ad[0]["is_ad"]=true;
            array_push($data["rows"],$ad[0]);

        }
        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

    public function read_history()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];
        $query=new Query();
        $lists= $query->table("fa_read_history")->alias("his")
            ->field("article.*,user.username,user.avatar,articletype.name")
            ->join("fa_article article","article.id=his.article_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->limit($offset,$page_size)->select();


        $count= $query->table("fa_read_history")->alias("his")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->count();



        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

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
        $model=new Article();

        $where=[];
        $where["article.id"]=$id;

        $query=new Query();
        $detail=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->find();
        file_put_contents("1",$detail->getLastSql());
        if($detail){
            $article=$model->where(["id"=>$id])->find();
            $article->read_count=$article->read_count+1;
            $article->show_count=$article->show_count+1;
            $article->save();

            //  增加阅读历史。
            $user_id=$this->auth->id;
            if($user_id!=$article->user_id){
                // 增加阅读历史记录.
                $his=new ReadHistory();

                $his->create(["user_id"=>$user_id,"article_id"=>$article->id,"time"=>time()]);
                file_put_contents("1",$his->getLastSql());
            }
        }
        $this->success("成功",$detail);
    }

    /*
    *添加文章
    * **/
    public function add()
    {

        try{
            $model=new \app\admin\model\Article();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            $data=[];
           // $data["files"]=$this->request->request('files','');
            $data["img"]=$this->request->request('img','');
            $data["title"]=$this->request->request('title','');
            $data["description"]=$this->request->request('description','');
            $data["content"]=$this->request->request('content','');
            $data["user_id"]=$user_id;
            $data["url"]=$this->request->request('url','');

            $data["is_reply"]=$this->request->request('is_reply','是');
            $data["is_mine"]=$this->request->request('is_mine','是');
            // 默认文章类型.
            $data["articletype_id"]=$this->request->request('articletype_id','1');
            $data["create_time"]=time();

            if(!$data["title"]||!$data["content"]){
                return $this->error(__('参数存在空'));
            }


            $model->create($data);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


}
