<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\PushList;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\admin\model\User;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Guanzhu extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 我关注的人
     *
     */
    public function Lists()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;


        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $query=new Query();
        $lists = $query->table("fa_guanzhu")->alias("guanzhu")
            ->field("guanzhu.*,user.nickname,user.avatar,user.bio")
            ->join("fa_user user","user.id=guanzhu.follow_id","left")
            ->where(['guanzhu.user_id'=> $user_id])
            ->limit($offset,$page_size)
            ->select();
        //dd($query->getLastSql());
        $count = $query->table("fa_guanzhu")->alias("guanzhu")
            ->field("guanzhu.*,user.nickname,user.avatar")
            ->join("fa_user user","user.id=guanzhu.follow_id","left")
            ->where(['guanzhu.user_id' => $user_id])
            ->count();


        foreach ($lists as &$l){
                // 查找follow_id 是否关注了它。
            $l["follow_id_at_user_id"]=false;

            $model= new \app\admin\model\Guanzhu();
            $temp=$model->where(["user_id"=>$l["follow_id"],"follow_id"=>$l["user_id"]])->find();
            if(!empty($temp)){
                $l["follow_id_at_user_id"]=true;
            }

        }

        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $data);

    }


    /**
     * 我关注个数
     *
     */
    public function count()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Guanzhu());
        $lists = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.user_id' => $user_id])
            ->where('user.id=guanzhu.follow_id')
            ->count();
        $this->success("成功", $lists);
    }


    /**
     * 关注我的人
     *
     */
    public function follow()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;


        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $query=new Query();
        $lists = $query->table("fa_guanzhu")->alias("guanzhu")
            ->field("guanzhu.*,user.nickname,user.avatar,user.bio")
            ->join("fa_user user","user.id=guanzhu.user_id","left")
            ->where(['guanzhu.follow_id'=> $user_id])
            ->limit($offset,$page_size)
            ->select();
        
        $count = $query->table("fa_guanzhu")->alias("guanzhu")
            ->field("guanzhu.*,user.nickname,user.avatar")
            ->join("fa_user user","user.id=guanzhu.user_id","left")
            ->where(['guanzhu.follow_id' => $user_id])
            ->count();

        foreach ($lists as &$l){
            // 查找follow_id 是否关注了它。
            $l["follow_id_at_user_id"]=false;

            $model= new \app\admin\model\Guanzhu();
            $temp=$model->where(["user_id"=>$l["follow_id"],"follow_id"=>$l["user_id"]])->find();
            if(!empty($temp)){
                $l["follow_id_at_user_id"]=true;
            }

        }

            $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$this->auth->id])->find();
            $flag->follow_flag=0;
            $flag->save();



        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $data);
    }


    /**
     * 我关注个数
     *
     */
    public function follow_count()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Guanzhu());
        $lists = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.follow_id' => $user_id])
            ->count();
        $this->success("成功", $lists);
    }


    /*
    *添加关注
    * **/
    public function add()
    {

        try {
            $data = [];
            $model = new \app\admin\model\Guanzhu();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $follow_id = $this->request->request('follow_id');


            if (!$follow_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            if (!User::getById($follow_id)) {
                return $this->error(__('被关注人不存在'));
            }
            if ($model->where(['user_id'=>$user_id,'follow_id'=>$follow_id])->select()) {
                return $this->error(__('已经关注了'));
            }

            if($model->where(['user_id'=>$user_id])->count()>200){
                return $this->error(__('关注数已经达到200'));
            }

            $test= $model->create([
                'user_id' => $user_id, 'follow_id' => $follow_id, 'time' => time()
            ]);

            //  添加关注
            $pushModel=new PushList();

            $temp=[
                "user_id"=>0,
                "push_type_id"=>2,
                "content"=>\GuzzleHttp\json_encode($test),
                "create_time"=>time()
            ];
            $pushModel->create($temp);


            // 为作者添加评论
            $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$follow_id])->find();
            if(empty($flag)){
                return $this->success();
            }

            $flag->follow_flag=1;
            $flag->save();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /*
*删除收藏
* **/
    public function delete()
    {

        try {
            $model = new \app\admin\model\Guanzhu();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $follow_id = $this->request->request('follow_id');


            if (!$follow_id) {
                return $this->error(__('参数存在空'));
            }

            $model->where(['user_id' => $user_id, 'follow_id' => $follow_id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    public function test(){
         (new \app\admin\model\Guanzhu())->initUser(9999);
    }

}
