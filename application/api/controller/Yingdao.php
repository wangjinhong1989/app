<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Yingdao extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $time=time();
        $lists=(new \app\admin\model\Yingdao())->where(["end_time"=>["egt",$time],"begin_time"=>["elt",$time]])->select();

        //$lists=collection($lists)->toArray();
        foreach ($lists as $k=> $list){

            $temp=explode(",",$list["files"]);

            foreach ($temp as &$v){
                $v="http://app.bixiaogui.com".$v;
            }

            $lists[$k]->files=implode(",",$temp);
        }
        $this->success("成功",$lists);

    }
}
