<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
/**
 * 首页接口
 */
class Ad extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=Cache::get('ad_list');
        if(!$lists){
            $lists=(new Guanggao())->where(['status'=>0])->select();
            Cache::set('ad_list',$lists,0);
        }

        $this->success($lists);
    }
}
