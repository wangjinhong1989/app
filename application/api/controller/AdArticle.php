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
class AdArticle extends Api
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
        $lists=$model->table("fa_ad_article")->where(["end_time"=>["egt",$time],"start_time"=>["elt",$time]])->orderRaw("rand()")->limit(0,1)->select();

        $temp=array();
        foreach ($lists as $k=>&$list){
            $images=explode(",",$list["images"]);
            foreach ($images as $image){
                $data=$list;
                unset($data["images"]);
                $data["image"]=$image;
                array_push($temp,$data);
            }
        }
        $this->success("成功",$temp);
    }
}
