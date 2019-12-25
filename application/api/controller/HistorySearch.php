<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class HistorySearch extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];
        $query=new Query();
        $lists= $query->table("fa_read_history")->alias("his")
            ->field("article.*,user.username,user.avatar,articletype.name")
            ->join("fa_article article","article.id=his.article_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->limit($offset,$page_size)->select();


        $count= $query->table("fa_read_history")->alias("his")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->count();



        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }




    /*
    *添加收藏
    * **/
    public function add()
    {

        try{
            $data=[];
            $model=new SearchHistory();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $word=$this->request->request('word');

            if(!$word){
                return $this->error(__('参数存在空'));
            }

            $model->create([
                'user_id'=>$user_id,'word'=>$word,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


    /*
*清除收藏
* **/
    public function delete()
    {

        try{
            $model=new SearchHistory();
            $user = $this->auth->getUser();
            $user_id=$user->id;

            $model->where(['user_id'=>$user_id])->delete();

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }
}
