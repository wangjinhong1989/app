<?php

namespace app\api\controller;


use app\admin\model\Article;
use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Reply extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $my_id=$this->auth->id;
        $where=[];
        $status=$this->request->request("status","");
        if($status){
            $where["status"]=["eq",$status];
        }

        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["user_id"]=["eq",$user_id];
        }else {
            $user_id=$my_id;
        }
        $parent_id=$this->request->request("parent_id","");
        if($parent_id){
            $where["parent_id"]=["eq",$parent_id];
        }

        $article_id=$this->request->request("article_id","");
        if($article_id){
            $where["article_id"]=["eq",$article_id];
        }


        if(!$article_id&&!$parent_id&&!$user_id){
            $this->error("参数错误","");
        }
        $query=new Query();
        $lists=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($where)
            ->limit($offset,$page_size)->order("reply.id desc")->select();

        $count=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($status)
           ->count();

        //  是我的文章，就标识 is_mine
        foreach ($lists as &$l){
            if($l["author_id"]==$my_id){
                $l["is_my_article"]="是";
            }else {
                $l["is_my_article"]="否";
            }
        }
        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



    // 文章评论接口， 显示标题和数字
    public  function group_by_article(){

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $where=[];

        $user_id=$this->auth->id;
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }

        $query=new Query();
        $lists=$query->table("fa_article")->alias("article")->field("article.*,user.username,user.avatar,count(reply.id) as reply_count")
            ->join("fa_reply reply","reply.article_id=article.id")
            ->join("fa_user user","user.id=article.user_id")
            ->where($where)
            ->limit($offset,$page_size)->order("article.id desc")->select();

        $count=$query->table("fa_article")->alias("article")->field("article.*,user.username,user.avatar,count(reply.id) as reply_count")
            ->join("fa_reply reply","reply.article_id=article.id")
            ->join("fa_user user","user.id=article.user_id")
            ->where($where)
           ->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);

    }


    /*
    *添加收藏
    * **/
    public function add()
    {

        try{
            $data=[];
            $model=new \app\admin\model\Reply();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $parent_id=$this->request->request('parent_id',0);
            $article_id=$this->request->request('article_id',0);
            $content=$this->request->request('content',"");

            if(!$article_id||!$content){
                return $this->error(__('参数存在空'));
            }

            $model->create([
                'user_id'=>$user_id,'article_id'=>$article_id,"parent_id"=>$parent_id,"content"=>$content,'createtime'=>time(),"status"=>"审核"
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


/*
*删除某个评论
* **/
    public function delete()
    {

        try{
            $model=new \app\admin\model\Reply();
            $user = $this->auth->getUser();
            $user_id=$user->id;
            $id=$this->request->request('id',0);
            $model->where(['id'=>$id])->delete();
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


    /*
    *更新状态, 审核该文章.
    * **/
    public function update()
    {

        try{
            $model=new \app\admin\model\Reply();
            $user = $this->auth->getUser();
            $user_id=$user->id;
            $id=$this->request->request('id',0);
            $status=$this->request->request('status',"有效");

            $reply=$model->where(["id"=>$id,"user_id"=>$user_id])->find();
            if(!$reply){
                return  $this->error("文章不存在");
            }
            $reply->status=$status;
            $reply->save();
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
