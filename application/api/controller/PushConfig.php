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
            //$id = $this->request->request('id',"0");
//            $is_accept_notify = $this->request->request('is_accept_notify',"是");
//            $is_article_notify = $this->request->request('is_article_notify',"是");
//            $is_kuaixun_notify = $this->request->request('is_kuaixun_notify',"是");
//            $is_follow_notify= $this->request->request('is_follow_notify',"是");
            $param= $this->request->request('param',"");


            $info=$model->where(["user_id"=>$user_id])->find();

            if(!$param){
                return $this->error(__('参数存在空'));
                die;
            }

            if(!$info){
                $model->create(
                    [
                        "is_accept_notify"=>"是",
                        "is_article_notify"=>"是",
                        "is_kuaixun_notify"=>"是",
                        "is_follow_notify"=>"是",
                        "need_voice"=>"是",
                        "status"=>"是",
                        "create_time"=>time(),

                    ]
                );
            }
            $info=$model->where(["user_id"=>$user_id])->find();
            if($param=="is_accept_notify"){
                if($info->is_accept_notify=="是"){
                    $info->is_accept_notify="否";
                }else {
                    $info->is_accept_notify="是";
                }
            }
            if($param=="is_article_notify"){
                if($info->is_article_notify=="是"){
                    $info->is_article_notify="否";
                }else {
                    $info->is_article_notify="是";
                }
            }

            if($param=="is_kuaixun_notify"){
                if($info->is_kuaixun_notify=="是"){
                    $info->is_kuaixun_notify="否";
                }else {
                    $info->is_kuaixun_notify="是";
                }
            }

            if($param=="is_follow_notify"){
                if($info->is_follow_notify=="是") {
                    $info->is_follow_notify = "否";
                }
                else {
                        $info->is_follow_notify="是";
                    }
            }

            if($param=="need_voice"){
                if($info->need_voice=="是"){
                    $info->need_voice="否";
                }else {
                    $info->need_voice="是";
                }
            }

            $info->save();
            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }


    public function follow_config(){

        $model=new \app\admin\model\Guanzhu();

        $time1=microtime(true);
        $id=$this->request->request("id",0);
        $info=$model->where(["id"=>$id,"user_id"=>$this->auth->id])->find();
        if(empty($info)){
            return $this->error("参数错误");
        }

        if($info->is_push=="是")
        $info->is_push="否";
        else{
            $info->is_push="是";
        }

        $info->save();
        $time2=microtime(true);
        return $this->success([$time2,$time1,$time2-$time1]);
    }
}
