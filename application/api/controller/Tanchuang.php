<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Tanchuang extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    /**
     * 首页
     *
     */
    public function get_one()
    {
        $where=[];
        $where["status"]=["eq","显示"];
        $where["begin_time"]=["elt",time()];
        $where["end_time"]=["egt",time()];

        $lists=(new \app\admin\model\Tanchuang())->where($where)->find();

        if($lists){

            $temp=explode(",",$lists["image"]);
            foreach ($temp as &$t){
                $t="http://app.biyouliao8.com".$t;
            }
            $lists["images"]=$temp;
        }
        $this->success("成功",$lists);
    }


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

        $where["begin_time"]=["elt",time()];
        $where["end_time"]=["egt",time()];

        $model=(new \app\admin\model\Tanchuang());
        $lists=$model->where($where)->order("paixu","asc")->orderRaw("rand()")->limit($offset,$page_size)->select();
        $count=$model->where($where)->count();


        foreach ($lists as &$l){
            $temp=explode(",",$l["image"]);
            foreach ($temp as &$t){
                $t="http://app.biyouliao8.com".$t;
            }
            $l["images"]=$temp;

        }
        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);

        $this->success("成功",$lists);
    }



}
