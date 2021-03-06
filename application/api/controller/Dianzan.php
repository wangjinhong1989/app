<?php

namespace app\api\controller;
use app\admin\model\Article;
use app\admin\model\PushList;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Dianzan extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 我点赞的列表
     *
     */
    public function Lists()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Dianzan());
        $lists = $model->alias('dianzan')
            ->with(['user','article'])
            ->field("dianzan.*,user.username,user.avatar,article.title,article.img")
            ->where(['dianzan.user_id' => $user_id])
            ->where('user.id=dianzan.at_id')
            ->where('article.id=dianzan.article_id')
            ->select();
        //echo   $model->getLastSql();
        foreach($lists as  $k=>$value){
            unset($lists[$k]['user']);
            unset($lists[$k]['article']);
        }
        $this->success("成功", $lists);
    }

    /**
     * 我点赞的列表
     *
     */
    public function count()
    {
        $user_id = $this->auth->id;

        $model = (new \app\admin\model\Dianzan());
        $lists = $model->where(['dianzan.user_id' => $user_id])
            ->count();

        $this->success("成功", $lists);
    }

    /**
     * 点赞我的人
     *
     */
    public function at_me_lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $my_id=$this->auth->id;
        $where=[];


        $where["reply_list.user_id"]=["eq",$this->auth->id];


        $query=new Query();
        $lists=$query->table("fa_dianzan")->alias("dianzan")->join("fa_reply_list reply_list","reply_list.id=dianzan.at_id ")->field("reply_list.*,dianzan.at_id")
            ->where($where)
            ->limit($offset,$page_size)->order("dianzan.id desc")->select();

        $count=$query->table("fa_dianzan")->alias("dianzan")->join("fa_reply_list reply_list","reply_list.id=dianzan.at_id ")->field("reply_list.*,dianzan.at_id")
            ->where($where)
            ->count();



        $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$this->auth->id])->find();
        $flag->dianzan=0;
        $flag->save();

        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

    /**
     * 点赞我的人
     *
     */
    public function at_me_count()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

//        $model = (new \app\admin\model\Dianzan());
//        $lists = $model
//            ->with(['user','article'])
//            ->field("dianzan.*,user.username,user.avatar,article.title,article.url")
//            ->where(['dianzan.at_id' => $user_id])
//            ->where('user.id=dianzan.user_id')
//            ->where('article.id=dianzan.article_id')
//            ->count();

        $query= new Query();
        $count=$query->table("fa_dianzan")->alias("dianzan")
            ->join("fa_reply reply","reply.id=dianzan.at_id")->where(["reply.user_id"=>$this->auth->id])->count();
        $this->success("成功", $count);
    }

    /*
    *添加收藏
    * **/
    public function add()
    {

        try {

            $data = [];
            $model = new \app\admin\model\Dianzan();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $reply_id= $this->request->request('reply_id');


            if (!$reply_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            $reply=\app\admin\model\Reply::getById($reply_id);

            if (!$reply) {
                return $this->error(__('评论不存在'));
            }

            $info=(new \app\admin\model\Dianzan())->where(['at_id'=>$reply_id,'user_id'=>$user_id])->select();
            if($info)
                return $this->error(__('已经点赞了'));
            $test=$model->create([
                'user_id' => $user_id, 'at_id' => $reply_id, 'time' => time()
            ]);

            // 点赞的信息列表。
            $pushModel=new PushList();

            $temp=[
                "user_id"=>$this->auth->id,
                "push_type_id"=>4,
                "user_ids"=>$reply->user_id,// 给所有人发。
                "content"=>$this->auth->username."给您点赞了",
                "param_json"=>json_encode($reply)
            ];
            $pushModel->create($temp);

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /*
*删除点赞
* **/
    public function delete()
    {

        try {
            $model = new \app\admin\model\Dianzan();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $reply_id = $this->request->request('reply_id');


            if (!$reply_id) {
                return $this->error(__('参数存在空'));
            }

            $model->where(['at_id' => $reply_id,"user_id"=>$user_id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

}
