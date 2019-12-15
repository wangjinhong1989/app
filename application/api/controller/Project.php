<?php

namespace app\api\controller;

use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Project extends Api
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

        $model = (new \app\admin\model\Project());
        $lists = $model
            ->where(['status'=>'显示'])
            ->select();
        $this->success("成功", $lists);
    }



}
