<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\admin\model\User;
use app\common\controller\Api;

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

        $model = (new \app\admin\model\Guanzhu());
        $lists = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.user_id' => $user_id])
            ->where('user.id=guanzhu.follow_id')
            ->limit($offset,$page_size)
            ->select();

        $count = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.user_id' => $user_id])
            ->where('user.id=guanzhu.follow_id')
            ->count();

        foreach($lists as $k=>$value){
            unset($lists[$k]['user']);
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

        $model = (new \app\admin\model\Guanzhu());
        $lists = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.follow_id' => $user_id])
            ->where('user.id=guanzhu.user_id')
            ->limit($offset,$page_size)
            ->select();

        $count = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.follow_id' => $user_id])
            ->where('user.id=guanzhu.user_id')
            ->count();
        foreach($lists as $k=>$value){
            unset($lists[$k]['user']);
        }

        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $lists);
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

            $model->create([
                'user_id' => $user_id, 'follow_id' => $follow_id, 'time' => time()
            ]);

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

}
