<?php

namespace app\api\controller;

use app\admin\model\TanchuangBack;
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
        if($this->auth->id){
            $time=Session::get("tanchuang".$this->auth->id);
            $time=intval($time);
            if($time+60<time()){
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
                Session::set("tanchuang".$this->auth->id,time());
                return $this->success("成功",$lists);
            }else{
                return $this->success("成功",[]);
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
       return $this->success("成功",$lists);
    }



    public function temp(){
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
    /**
     * 首页
     *
     */
    public function Lists1()
    {


        $temp=$this->auth->id;
        dd("auth_id".$temp);
        if($temp){
            dd("login:".$this->auth->id);
            $time=Cache::get("tanchuang".$this->auth->id);
            dd("time:".$time);
            $time=intval($time);
            dd("time:".$time);
            if($time+24*3600<time()){

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



                Cache::set("tanchuang".$this->auth->id,time(),24*3600);
                dd(" login 1");
                return $this->success("登录后请求到的数据,新的",$data1);

            }else{
                dd(" login 2");
                return $this->success("登录后请求到的数据，旧的",[]);
            }

        }


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

        dd("not login");
        return $this->success("没有登录请求，请求的数据",$data1);
    }


    public function lists(){

        $model= new \app\admin\model\Tanchuang();
        $temp=$this->auth->id;
        if($temp){
            $where=[];
            $where["user_id"]=$temp;
            $where["create_time"]=["egt",time()-24*3600];
            $exp=array_column((new TanchuangBack())->where($where)->field("tanchuan_id")->select(),"tanchuan_id");

            if(empty($exp))
                $exp=[0];
            $data=$model->getOne($exp);


            if(!empty($data)){
                $m= new TanchuangBack();

                $find= $m->where(["user_id"=>$temp,"tanchuan_id"=>$data[0]->id])->find();
                if($find){
                    $find->create_time=time();
                    $find->save();
                }else{
                    $m->create(["tanchuan_id"=>$data[0]->id,"user_id"=>$temp,"create_time"=>time()]);
                }

            }
            return $this->success("",$this->fmt($data));
        }else {
            // 未登路.
            $data=$model->getOne();
            return $this->success("",$this->fmt($data));
        }

    }

    public function fmt($lists){
                $data1=[];
        foreach ($lists as &$l){
            $temp=explode(",",$l["image"]);
            foreach ($temp as &$t){
                $l["image"]=$t;
                array_push($data1,$l);
            }

        }

        return $data1;
    }
}
