<?php

namespace app\api\controller;

use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Project extends Api
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

        $model = (new \app\admin\model\Project());
        $lists = $model
            ->where(['status'=>'显示'])
            ->select();
        $this->success("成功", $lists);
    }

    /*
    *更新项目阅读
    * **/
    public function update()
    {

        try{
            $model=new \app\admin\model\Project();
            $user = $this->auth->getUser();
            $id=$this->request->request('id');

            if(!$id){
                return $this->error(__('参数存在空'));
            }
            if(!$model::getById($id)){
                return $this->error(__('项目不存在'));
            }
            $info=$model::getById($id);
            $info->hot=$info->hot+1;
            $info->rank=$info->rank+1;
//            $model->save(['hot'=>$info->hot,'rank'=>$info->rank],['id'=>$id]);
            $info->save();
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


}