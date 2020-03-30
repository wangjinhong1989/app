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
class ZhengjianQiye extends Api
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
        $lists=( new \app\admin\model\ZhengjianQiye())->where(["user_id"=>$user_id])
            ->find();
        $this->success("成功",$lists);
    }


    /*
    *添加反馈
    * **/
    public function add1()
    {

        try{
            $model=new \app\admin\model\ZhengjianQiye();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            $data=[];
            $data["image"]=$this->request->request('image');
            $data["certificates_type"]=$this->request->request('certificates_type',"企业营业执照");
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


    /*
   *添加反馈
   * **/
    public function add2()
    {

        try{
            $model=new \app\admin\model\ZhengjianQiye();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            $qiye= $model->where(["user_id"=>$user_id])->find();

            if(empty($qiye)){
                return $this->error("企业认证第一步未完成");
            }
            $data=[];
            $data["faren_name"]=$this->request->request('faren_name');
            $data["faren_number"]=$this->request->request('faren_number');
            $data["images"]=$this->request->request('images');

            foreach ($data  as $k=>$value){
                if(empty($value)){
                    return $this->error("参数".$k."为空");
                }
            }

            $qiye->save($data);
            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }




}
