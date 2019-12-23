<?php

namespace app\api\controller;
use app\common\controller\Api;
/**
 * 首页接口
 */
class AuthenticationPersonal extends Api
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
        $lists=( new \app\admin\model\AuthenticationPersonal())->where(["user_id"=>$user_id])
            ->find();
        $this->success("成功",$lists);
    }


    /*
    *添加反馈
    * **/
    public function add()
    {

        try{
            $model=new \app\admin\model\AuthenticationPersonal();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $type=$this->request->request('type','个人认证');
            $files=$this->request->request('files');
            $certificates_type=$this->request->request('certificates_type');
            $name=$this->request->request('name');
            $certificates_number=$this->request->request('certificates_number');
            $note=$this->request->input('note','');

            if(!$type||!$files||!$certificates_type||!$name||!$certificates_number){
                return $this->error(__('参数存在空'));
            }


            if($model->where(['type'=>$type,'user_id'=>$user_id])->select()){
                return $this->error(__('已上传，请不要重复提交'));
            }
            $model->create([
                'user_id'=>$user_id,'type'=>$type,'name'=>$name,
                'certificates_number'=>$certificates_number,
                'certificates_type'=>$certificates_type,
                'files'=>$files,'note'=>$note,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
