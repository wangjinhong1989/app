<?php

namespace app\api\controller;


use app\admin\model\HotSearch;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Hot extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        $data=[];
        $lists=( new HotSearch())->where(['status'=>0])->limit($offset,$page_size)->select();
        $count=( new HotSearch())->where(['status'=>0])->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);

        $this->success("成功",$lists);

    }
}
