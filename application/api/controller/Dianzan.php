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
            $article_id = $this->request->request('article_id');


            if (!$article_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            $article=Article::getById($article_id);
            if (!$article) {
                return $this->error(__('文章不存在'));
            }

            $model->create([
                'user_id' => $user_id, 'article_id' => $article_id,'at_id'=>$article->user_id, 'time' => time()
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
            $model = new \app\admin\model\Dianzan();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $article_id = $this->request->request('article_id');


            if (!$article_id) {
                return $this->error(__('参数存在空'));
            }
            if (!Article::getById($article_id)) {
                return $this->error(__('文章不存在'));
            }

            $model->where(['user_id' => $user_id, 'article_id' => $article_id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

}
