<?php

namespace app\api\controller;
use app\admin\model\Article;
use app\common\controller\Api;

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
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Dianzan());
        $lists = $model->alias('dianzan')
            ->with(['user','article'])
            ->field("dianzan.*,user.username,user.avatar,article.title,article.img")
            ->where(['dianzan.user_id' => $user_id])
            ->where('user.id=dianzan.at_id')
            ->where('article.id=dianzan.article_id')
            ->count();

        $this->success("成功", $lists);
    }

    /**
     * 点赞我的人
     *
     */
    public function at_me_lists()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Dianzan());
        $lists = $model
            ->with(['user','article'])
            ->field("dianzan.*,user.username,user.avatar,article.title,article.url")
            ->where(['dianzan.at_id' => $user_id])
            ->where('user.id=dianzan.user_id')
            ->where('article.id=dianzan.article_id')
            ->select();
        foreach($lists as  $k=>$value){
            unset($lists[$k]['user']);
            unset($lists[$k]['article']);
        }
        $this->success("成功", $lists);
    }

    /**
     * 点赞我的人
     *
     */
    public function at_me_count()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Dianzan());
        $lists = $model
            ->with(['user','article'])
            ->field("dianzan.*,user.username,user.avatar,article.title,article.url")
            ->where(['dianzan.at_id' => $user_id])
            ->where('user.id=dianzan.user_id')
            ->where('article.id=dianzan.article_id')
            ->count();

        $this->success("成功", $lists);
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
            $model->create([
                'user_id' => $user_id, 'at_id' => $reply_id, 'time' => time()
            ]);

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
            $id = $this->request->request('id');


            if (!$id) {
                return $this->error(__('参数存在空'));
            }

            $model->where(['id' => $id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

}
