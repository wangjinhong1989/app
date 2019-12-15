<?php

namespace app\api\controller;


use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;
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
        $user_id=$user->id;
        $lists=( new Shoucang())->alias("shoucang")->with((new Article())->getTable("article")." as article")->where(['shoucang.user_id'=>$user_id,'article.id=shoucang.article_id'])->select("shoucang.*,article.title");
        $this->success($lists);
    }

    /*
    *添加收藏
    * **/
    public function add()
    {

        try{
            $data=[];
            $model=new Shoucang();
            $user = $this->auth->getUser();
            $user_id=$user->id;
            $article_id=$this->request->request('article_id');


            if(!$article_id){
                return $this->error(__('参数存在空'));die;
            }
            if(!Article::getById($article_id)){
                return $this->error(__('文章不存在'));
            }

            $model->create([
                'user_id'=>$user_id,'article_id'=>$article_id,'time'=>time()
            ]);

            return $this->success('123',$model->getLastSql());
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

    /*
*删除收藏
* **/
    public function delete()
    {

        try{
            $model=new Shoucang();
            $user = $this->auth->getUser();
            $user_id=$user->id;
            $article_id=$this->request->request('article_id');


            if(!$article_id){
                return $this->error(__('参数存在空'));
            }
            if(!Article::getById($article_id)){
                return $this->error(__('文章不存在'));
            }

            $model->where(['user_id'=>$user_id,'article_id'=>$article_id])->delete();

            return $this->success('123',$model->getLastSql());
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
