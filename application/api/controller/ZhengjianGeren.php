<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\Session;

/**
 * 首页接口
 */
class ZhengjianGeren extends Api
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
        $lists=( new \app\admin\model\ZhengjianGeren())->where(["user_id"=>$user_id])
            ->find();
        $this->success("成功",$lists);
    }


    /*
    *添加反馈
    * **/
    public function add()
    {

        try{
            $model=new \app\admin\model\ZhengjianGeren();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            $data=[];
            $data["images"]=$this->request->request('images');
            $data["certificates_type"]=$this->request->request('certificates_type',"身份证");
            $data["name"]=$this->request->request('name');
            $data["number"]=$this->request->request('number');

            foreach ($data  as $k=>$value){
                if(empty($value)){
                    return $this->error("参数".$k."为空");
                }
            }


            if($model->where(['user_id'=>$user_id])->select()){
                return $this->success(__('已上传，请不要重复提交'));
            }
            $data["create_time"]=time();
            $data["user_id"]=$user_id;
            $model->create($data);
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }



}
