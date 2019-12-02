<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

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

        Cache::store('redis')->clear();
        $lists=Cache::store('redis')->get('ad_list');
        if(!$lists){
            $lists=(new Guanggao())->where(['status'=>0])->select();
            Cache::store('redis')->set('ad_list',$lists,0);
        }

        $this->success($lists);
    }
}
