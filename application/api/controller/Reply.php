<?php

namespace app\api\controller;


use app\admin\model\Article;
use app\admin\model\PushList;
use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Reply extends Api
{
    protected $noNeedLogin = ["lists"];
    protected $noNeedRight = ['*'];

    /**
     *
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        if(!$this->auth->id){
            $my_id=0;
        }else
            $my_id=$this->auth->id;

        $where=[];
        $status=$this->request->request("status","");
        if($status){
            $where["status"]=["eq",$status];
        }

        //  表示某人的评论列表
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["user_id"]=["eq",$user_id];
        }
        $parent_id=$this->request->request("parent_id","");
        if($parent_id){
            $where["parent_id"]=["eq",$parent_id];
        }

        $article_id=$this->request->request("article_id","");
        if($article_id){
            $where["article_id"]=["eq",$article_id];
        }

        $author_id=$this->request->request("author_id",0);
        if($author_id){
            $where["author_id"]=["eq",$my_id];
        }


        if(!$article_id&&!$parent_id&&!$user_id){
            $this->error("参数错误","");
        }
        $query=new Query();
        $lists=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($where)
            ->limit($offset,$page_size)->order("reply.id desc")->select();

        $count=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($where)
           ->count();

        //  是我的文章，就标识 is_mine
        foreach ($lists as &$l){
            if($l["author_id"]==$my_id){
                $l["is_my_article"]="是";
                if($article_id){
                    $article=(new Article())->where(["id"=>$article_id])->find();
                    if($article){
                        $article->is_read_reply_count=0;
                        $article->save();
                    }

                }


            }else {
                $l["is_my_article"]="否";
            }

            $l["createtime"]=formart_time(($l["createtime"]));
            $l["reply_time"]=formart_time($l["reply_time"]);
            foreach ($l as $key=>$value){
                if(is_null($value)){
                    $l[$key]="";
                }
            }



            $l["dianzan_count"]=(new Query())->table("fa_dianzan")->where(["at_id"=>$l["id"]])->count();

            $query=new Query();
            $temp=$query->table("fa_dianzan")->where(["at_id"=>$l["id"],"user_id"=>$my_id])->count();

            if($temp){
                $l["flag_dianzhan"]=true;
            }else
                $l["flag_dianzhan"]=false;

        }
        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }


    /**
     *获取回复我的评论
     *
     */
    public function get_my_reply()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $my_id=$this->auth->id;
        $where=[];


            $where["user_id"]=["eq",$my_id];

            $where["parent_id"]=["gt",0];


        $query=new Query();
        $lists=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($where)
            ->limit($offset,$page_size)->order("reply.id desc")->select();
        //dd($query->getLastSql());
        $count=$query->table("fa_reply_list")->alias("reply")->field("*")
            ->where($where)
            ->count();

        //  是我的文章，就标识 is_mine
        foreach ($lists as &$l){
            if($l["author_id"]==$my_id){
                $l["is_my_article"]="是";
            }else {
                $l["is_my_article"]="否";
            }

            $l["createtime"]=formart_time(($l["createtime"]));
            $l["reply_time"]=formart_time($l["reply_time"]);
            foreach ($l as $key=>$value){
                if(is_null($value)){
                    $l[$key]="";
                }
            }
        }
        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$this->auth->id])->find();
        $flag->reply_flag=0;
        $flag->save();

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
        $where["reply_count"]=["gt",0];
//        $where["is_read_reply_count"]=["gt",0];
        $user_id=$this->auth->id;
        if($user_id){
            $where["user_id"]=["eq",$user_id];

        }

        $query=new Query();
        $lists=$query->table("fa_my_reply_count")->alias("my_reply")->field("*")
            ->where($where)
            ->limit($offset,$page_size)->order("is_read_reply_count desc")->select();

        $count=$query->table("fa_my_reply_count")->alias("my_reply")->field("id")
            ->where($where)
            ->count();


        foreach ($lists as &$l){
            $l["create_time"]=formart_time($l["createtime"]);
            $l["reply_count"]=$l["is_read_reply_count"];


        }
        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$this->auth->id])->find();
        $flag->comment_flag=0;
        $flag->save();

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);

    }


    /*
    *添加评论
    * **/
    public function add()
    {


        $info=$this->auth->getUserinfo();
        if($info["status"]=="hidden"){
            return $this->error("您已经被封号，不能发言");
        }

        try{
            $data=[];
            $model=new \app\admin\model\Reply();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            //  不需要
            //$parent_id=$this->request->request('parent_id',0);
            $parent_id=0;
            $article_id=$this->request->request('article_id',0);
            $content=$this->request->request('content',"");


            if(!$article_id||!$content){
                return $this->error(__('参数存在空'));
            }

            $article=(new Article())->where(["id"=>$article_id])->find();
            if($article->is_reply=="否"){
                return $this->error(__('文章不允许评论'));
            }


            $status="审核";
            if($article->user_id==$user_id){
                $status="有效";
                $article->reply_count=$article->reply_count+1;
                $article->save();
            }else{
                $article->reply_count=$article->reply_count+1;
                $article->is_read_reply_count=$article->is_read_reply_count+1;
                $article->save();
            }
            $test=$model->create([
                //'user_id'=>$user_id,'article_id'=>$article_id,"parent_id"=>$parent_id,"content"=>$content,'createtime'=>time(),"status"=>"审核"
                'user_id'=>$user_id,'article_id'=>$article_id,"parent_id"=>$parent_id,"content"=>$content,'createtime'=>time(),"status"=>$status
            ]);


            if($article->user_id!=$user_id) {
                // 点赞的信息列表。
                $pushModel = new PushList();

                $temp = [
                    "user_id" => $this->auth->id,
                    "push_type_id" => 1,
                    "user_ids" => $article->user_id,//
                    "content" => $this->auth->username . "留言了您的文章",
                    "param_json" => json_encode($test)
                ];
                $pushModel->create($temp);


                // 查找作者。
                $article = (new Article())->where(["id" => $article_id])->find();
                if (empty($article)) {
                    return $this->success();
                }
                // 为作者添加评论
                $flag = (new \app\admin\model\FlagMessage())->where(["user_id" => $article->user_id])->find();
                if (empty($flag)) {
                    return $this->success();
                }

                $flag->comment_flag = 1;
                $flag->save();
            }
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

            $reply=$model->where(["id"=>$id])->find();
            if(!$reply){
                return  $this->error("文章不存在");
            }
            $reply->status=$status;
            $reply->save();


            // 为作者添加评论
            $flag = (new \app\admin\model\FlagMessage())->where(["user_id" => $reply->user_id])->find();
            if (empty($flag)) {
                return $this->success();
            }

            $flag->system_flag = 1;
            $flag->save();

            if($status=="有效"){
                $modelMessage=new \app\admin\model\SystemMessage();
                $modelMessage->create([
                    "user_id"=>$reply->user_id,
                    "status"=>"未读",
                    "time"=>time(),
                    "content"=>"恭喜您的留言入选为精选留言"
                ]);
            }
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

    /*
    *回复评论
    * **/
    public function reply_content()
    {

        $info=$this->auth->getUserinfo();
        if($info["status"]=="hidden"){
            return $this->error("您已经被封号，不能回复");
        }

        try{
            $model=new \app\admin\model\Reply();
            $user = $this->auth->getUser();
            $user_id=$user->id;
            $id=$this->request->request('id',0);
            $reply_content=$this->request->request('reply_content',"");
            $parent_id=$this->auth->id;

            $reply=$model->where(["id"=>$id])->find();
            if(!$reply){
                return  $this->error("评论不存在");
            }

            // 检查文章用户.

            $article=(new Article())->where(["id"=>$reply->article_id])->find();
            if(empty($article)){
                return  $this->error("文章不存在");
            }
//            if($article->is_reply!="是"){
//                return  $this->error("不允许评论");
//            }

            $reply->reply_content=$reply_content;
            $reply->parent_id=$parent_id;
            $reply->reply_time=time();
            $reply->save();

            // 为作者添加评论
            $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$reply->user_id])->find();
            if(empty($flag)){
                return $this->success();
            }

            $flag->reply_flag=1;
            $flag->save();
            $reply=$model->where(["id"=>$id])->find();

            // 点赞的信息列表。
            $pushModel=new PushList();

            $temp=[
                "user_id"=>$this->auth->id,
                "push_type_id"=>1,
                "user_ids"=>$reply->user_id,//
                "content"=>$this->auth->username."回复了您",
                "param_json"=>json_encode($reply)
            ];
            $pushModel->create($temp);




            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
