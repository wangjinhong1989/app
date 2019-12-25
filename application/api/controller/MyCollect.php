<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class MyCollect extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
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

        $model = (new Query());
        $data["rows"] = $model->table("fa_shoucang")->alias('shoucang')
                ->join("fa_article article","article.id=shoucang.article_id")
                ->where(['shoucang.user_id' => ["=",$user_id]])
                ->limit($offset,$page_size)
                ->select();

        $data["count"] = $model->table("fa_shoucang")->alias('shoucang')
            ->join("fa_article article","article.id=shoucang.article_id")
            ->where(['shoucang.user_id' => ["=",$user_id]])
            ->count();

        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $data);
    }

    /*
    *添加收藏
    * **/
    public function add()
    {

        try {
            $data = [];
            $model = new Shoucang();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $article_id = $this->request->request('article_id');


            if (!$article_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            if (!Article::getById($article_id)) {
                return $this->error(__('文章不存在'));
            }

            if ($model->where(["user_id"=>["=",$user_id],"article_id"=>["=",$article_id]])->find()) {
                return $this->error(__('已经添加收藏'));
            }

            $model->create([
                'user_id' => $user_id, 'article_id' => $article_id, 'time' => time()
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
            $model = new Shoucang();
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
