<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Recomment extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $where=$data=[];
        $where["status"]=["eq","显示"];
        $url_type=$this->request->request("url_type","");
        if($url_type){
            $where["url_type"]=["eq",$url_type];
        }

        $lists=(new \app\admin\model\Recomment())->where($where)->limit($offset,$page_size)->select();
        $count=(new \app\admin\model\Recomment())->where($where)->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);

        $this->success("成功",$lists);
    }
}
