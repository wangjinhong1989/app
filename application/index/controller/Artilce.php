<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Sms;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 */
class Artilce extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 文章列表
     */
    public function index()
    {
        var_dump(1111);
//        $model=new \app\admin\model\Article();
//        $lists=$model->where(['user_id'=>$this->auth->getUser()->id])->select();
//        $this->view->assign('title', __(''));
//        var_dump($lists);
//        $this->view->assign('lists', $lists);
//        return $this->view->fetch();
    }

}
