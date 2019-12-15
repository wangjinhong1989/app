<?php

namespace app\api\controller;

use app\admin\model\Shoucang;
use app\admin\model\Article;
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

}
