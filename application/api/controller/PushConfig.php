<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\admin\model\User;
use app\common\controller\Api;

/**
 * 首页接口
 */
class PushConfig extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function detail()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;
        $model=new \app\admin\model\PushConfig();
        $data=$model->where(["user_id"=>$user_id])->find();
        if(empty($data)){
            // 写入一条数据
            $model->create([
                'user_id' => $user_id, 'create_time' => time()
            ]);
            $data=$model->where(["user_id"=>$user_id])->find();
        }
        $this->success("成功", $data);
    }


    /*
    *添加关注
    * **/
    public function add()
    {

        try {
            $data = [];
            $model = new \app\admin\model\Guanzhu();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $follow_id = $this->request->request('follow_id');


            if (!$follow_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            if (!User::getById($follow_id)) {
                return $this->error(__('被关注人不存在'));
            }
            if ($model->where(['user_id'=>$user_id,'follow_id'=>$follow_id])->select()) {
                return $this->error(__('已经关注了'));
            }

            $model->create([
                'user_id' => $user_id, 'follow_id' => $follow_id, 'time' => time()
            ]);

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }


}
