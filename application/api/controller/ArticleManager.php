<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
/**
 * 首页接口
 */
class ArticleManager extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=( new Article())->where(['status'=>0])->select();
        $this->success($lists);
    }

}
