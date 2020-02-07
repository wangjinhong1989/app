<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Label extends Api
{
    protected $noNeedLogin = ['*'];
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
        $lists=( new \app\admin\model\Label())->where(['status'=>'显示'])->limit($offset,$page_size)->select();
        $count=( new \app\admin\model\Label())->where(['status'=>'显示'])->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
