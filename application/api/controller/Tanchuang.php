<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\Session;

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
        if(!empty($this->auth->id)){
            $time=Session::get("tanchuang".$this->auth->id);
            $time=intval($time);
            if($time+24*3600>time()){
                $where=[];
                $where["status"]=["eq","显示"];
                $where["begin_time"]=["elt",time()];
                $where["end_time"]=["egt",time()];

                $lists=(new \app\admin\model\Tanchuang())->where($where)->find();

                if($lists){

                    $temp=explode(",",$lists["image"]);
                    foreach ($temp as &$t){
                        $t=Config::get('api_url').$t;
                    }
                    $lists["images"]=$temp;
                }
                Session::push("tanchuang".$this->auth->id,time());
                $this->success("成功",$lists);
            }else{
                $this->success("成功",[]);
            }

        }

        $where=[];
        $where["status"]=["eq","显示"];
        $where["begin_time"]=["elt",time()];
        $where["end_time"]=["egt",time()];

        $lists=(new \app\admin\model\Tanchuang())->where($where)->find();

        if($lists){

            $temp=explode(",",$lists["image"]);
            foreach ($temp as &$t){
                $t=Config::get('api_url').$t;
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


        $data1=[];
        foreach ($lists as &$l){
            $temp=explode(",",$l["image"]);
            foreach ($temp as &$t){
                $l["image"]=$t;
                array_push($data1,$l);
            }

        }
        $data["page"]=$page;
        $data["rows"]=$data1;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);

        $this->success("成功",$data1);
    }



}
