<?php

namespace app\api\controller;


use app\admin\model\Articletype;
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
        $user_id = $user->id;

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        $data=[];

        $model = (new Shoucang());
        $data["rows"] = $model->alias('shoucang')->with(['article'])->limit($offset,$page_size)->where(['shoucang.user_id' => $user_id])->where('article.id=shoucang.article_id')->select();

        $data["count"] = $model->alias('shoucang')->with(['article'])->where(['shoucang.user_id' => $user_id])->where('article.id=shoucang.article_id')->count();

        $data["page"]=$page;

        foreach ($data["rows"] as $k=>&$v){

            $user=(new \app\admin\model\User())->where(["id"=>$v["article"]["user_id"]])->find();
            $v["article"]["author_name"]="";
            $v["article"]["author_avatar"]="";
            $v["article"]["create_time_text"]=formart_time($v["article"]->create_time);
            if($user){
                $v["article"]["author_name"]=$user->username;
                $v["article"]["author_avatar"]=$user->avatar;
            }
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $data);
    }

    /*
    *添加收藏
    * **/
    public function add()
    {

        try {

            dd(1);
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
            dd(2);
            return $this->success();
        } catch (Exception $e) {
            dd($e);
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

            $article_id=intval($article_id);
            if($article_id>0)
            $model->where(['user_id' => $user_id, 'article_id' => $article_id])->delete();
            else
                $model->where(['user_id' => $user_id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }


    // 是否为我的收藏
    public function  is_my_collect(){
        $model = new Shoucang();
        $user = $this->auth->getUser();
        $user_id = $user->id;
        $article_id = $this->request->request('article_id');
        $info=$model->where(['user_id' => $user_id, 'article_id' => $article_id])->find();

        if(!empty($info)){
            return $this->success("","是");
        }else
            return $this->success("","否");

    }
}
