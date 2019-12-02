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
        Cache::store('redis')->clear();

        $lists=Cache::store('redis')->get('ad_list');
        if(!$lists){
            echo "设置";
            $lists=(new Guanggao())->where(['status'=>0])->select();
            var_dump(Cache::store('redis')->set('ad_list',$lists,0));
            echo "end";
        }

        $this->success($lists);
    }
}
