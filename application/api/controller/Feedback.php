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
        $lists = $model->where(['user_id' => $user_id])
            ->select();

        $model1 = (new \app\admin\model\FeedbackReply());
        foreach($lists as $k=>$value){

            $lists[$k]['reply'] = $model1->where(['feedback_id' => $value['id']])
                ->select();
        }

        $this->success("成功", $lists);
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

            if(!$content||!$files||!$feedback_type){
                return $this->error(__('参数存在空'));
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
