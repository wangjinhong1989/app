<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\db\Query;

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
        $model=    new Query();

        $time=time();
        $lists=$model->table("fa_guanggao")->where(['status'=>'显示',"end_time"=>["lt",$time]])->orderRaw("rand()")->limit(0,1)->select();
        dd($model->getLastSql());
//        //phpinfo();
//        Cache::store('redis')->clear();
//        $lists=Cache::store('redis')->get('ad_list');
//        if(!$lists){
//            $lists=(new Guanggao())->where(['status'=>'显示'])->order("RAND()")->select();
//            Cache::store('redis')->set('ad_list',$lists,60);
//        }

        $this->success("成功",$lists);
    }
}
