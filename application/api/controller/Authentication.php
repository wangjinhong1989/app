<?php

namespace app\api\controller;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Authentication extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $authentication_type=$this->request->post("authentication_type");
        if(!$authentication_type)
            $this->error("缺少参数");
        $lists=( new \app\admin\model\Authentication())
            ->with("certificates")
            ->where(['authentication_type'=>$authentication_type])
            ->where('certificates.id=authentication.certificates_id')
            ->select();
        $this->success($lists);
    }


    /*
    *添加反馈
    * **/
    public function add()
    {

        try{
            $model=new \app\admin\model\Authentication();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $authentication_type=$this->request->request('authentication_type');
            $files=$this->request->request('files');
            $certificates_id=$this->request->request('certificates_id');
            $number=$this->request->request('number');
            $parent_id=$this->request->input('parent_id',0);

            if(!$authentication_type||!$certificates_id||!$number){
                return $this->error(__('参数存在空'));
            }

            if($authentication_type!="个人认证"&&$parent_id==0){
                return $this->error(__('参数存在空'));
            }

            if($model->where(['authentication_type'=>$authentication_type,'user_id'=>$user_id])->select()){
                return $this->error(__('已上传，请不要重复提交'));
            }
            $model->create([
                'user_id'=>$user_id,'authentication_type'=>$authentication_type,
                'number'=>$number,
                'parent_id'=>$parent_id,
                'files'=>$files,'certificates_id'=>$certificates_id,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
