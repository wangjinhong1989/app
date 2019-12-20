<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
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

        $lists=( new SearchHistory())->where(['user_id'=>$this->auth->getUser()->id])->select();
        $this->success("成功",$lists);
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
