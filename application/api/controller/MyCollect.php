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

        $model=( new Shoucang());
//            ->with(['article'])
       $lists= $model->alias('shoucang')
//           ->field("shoucang.id,article.title,article.content,article.img,article.url,article.show_count,article.read_count,article.is_reply,article.is_mine")
           ->with(['article','articletype'])
            ->where(['shoucang.user_id'=>$user_id])
            ->where('article.id=shoucang.article_id')
            ->select();

        foreach ($lists as $row) {

            $row->getRelation('articletype')->visible(['name']);
        }
        $this->success($lists,$user_id,$model->getLastSql());
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
