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
    public function update()
    {

        try {
            $data = [];
            $model= new \app\admin\model\PushConfig();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $id = $this->request->request('id',"0");
            $is_accept_notify = $this->request->request('is_accept_notify',"是");
            $is_article_notify = $this->request->request('is_article_notify',"是");
            $is_kuaixun_notify = $this->request->request('is_kuaixun_notify',"是");
            $is_follow_notify= $this->request->request('is_follow_notify',"是");

            $info=$model->where(["id"=>$id,"user_id"=>$user_id])->find();

            if(!$info||!$id||!$is_accept_notify||!$is_article_notify||!$is_kuaixun_notify||!$is_follow_notify){
                return $this->error(__('参数存在空'));
                die;
            }

            $arr=["是","否"];
            if(!in_array($is_accept_notify,$arr)||!in_array($is_article_notify,$arr)||!in_array($is_kuaixun_notify,$arr)||!in_array($is_follow_notify,$arr)){
                return $this->error(__('参数存在错误'));
            }
            $info->is_accept_notify=$is_accept_notify;
            $info->is_article_notify=$is_article_notify;
            $info->is_kuaixun_notify=$is_kuaixun_notify;
            $info->is_follow_notify=$is_follow_notify;
            $info->save();
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }


}
