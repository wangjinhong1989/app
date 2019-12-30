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
//        file_put_contents("1",$detail->getLastSql());
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

                $find=$his->where(["user_id"=>$user_id,"article_id"=>$article->id])->find();
                if($find){
                    $find->time=time();
                    $find->save();
                }else
                    $his->create(["user_id"=>$user_id,"article_id"=>$article->id,"time"=>time()]);
            }
        }

        $label=new \app\admin\model\Label();
        $detail->label_ids=[];
        if($detail->label_ids){
            $detail->label_ids=$label->where(['id'=>['in',$detail->label_ids]])->select();
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
            // 封面
            $data["img"]=$this->request->request('img','');
            $data["title"]=$this->request->request('title','');
            $data["description"]=$this->request->request('description','');
            $data["content"]=$this->request->request('content','');
            $data["user_id"]=$user_id;
            // 来源地址
            $data["url"]=$this->request->request('url','');

            // 是否允许回复
            $data["is_reply"]=$this->request->request('is_reply','是');
            // 是否原创
            $data["is_mine"]=$this->request->request('is_mine','是');
            // 默认文章类型.  资讯
            $data["articletype_id"]=intval($this->request->request('articletype_id','1'));
            // 标签ID .
            $data["label_ids"]=$this->request->request('label_ids','');
            $data["create_time"]=time();
            // 转载来源.
            $data["come_from"]=$this->request->request('come_from','');

            if(!$data["title"]||!$data["content"]){
                return $this->error(__('标题或者内容为空'));
            }

            // 这里要求传描述
            if($data["articletype_id"]==2){
                if(!$data["description"]){
                    return $this->error(__('描述不能为空'));
                }
            }else{
                if(!$data["img"]){
                    return $this->error(__('请上传封面'));
                }
            }

            $model->create($data);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


}
