<?php

namespace app\api\controller;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Feedback extends Api
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

        $model = (new \app\admin\model\Feedback());


        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        $lists = $model->where(['user_id' => $user_id])->limit($offset,$page_size)
            ->select();

        $count = $model->where(['user_id' => $user_id])->count();

        $model1 = (new \app\admin\model\FeedbackReply());
        foreach($lists as $k=>$value){

            $lists[$k]['reply'] = $model1->where(['feedback_id' => $value['id']])
                ->select();
        }

        $data=[];

        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功", $data);
    }


    /*
    *添加反馈
    * **/
    public function add()
    {

        try{
            $model=new \app\admin\model\Feedback();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $feedback_type=$this->request->request('feedback_type');
            $content=$this->request->request('content');
            $files=$this->request->request('files');

            if(!$content||!$feedback_type){
                return $this->error(__('参数存在空'));
            }



            $user=new \app\admin\model\User();

            if(!$user->auth_status($this->auth->id)){
                return $this->error("认证未通过");
            }


            $model->create([
                'user_id'=>$user_id,'feedback_type'=>$feedback_type,'files'=>$files,'content'=>$content,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
