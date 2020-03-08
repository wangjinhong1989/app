<?php

namespace app\api\controller;


use app\admin\model\HotSearch;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Discover extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Discover();
    }
    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $order=$this->request->request("order","paixu");
        $sort=$this->request->request("sort","asc");
        $offset=($page-1)*$page_size;

        $time=time();
        $data=[];
        $lists=( new \app\admin\model\Discover())->where(["end_time"=>["egt",$time],"begin_time"=>["elt",$time]])->limit($offset,$page_size)->order($order,$sort)->select();
        $count=( new \app\admin\model\Discover())->where(["end_time"=>["egt",$time],"begin_time"=>["elt",$time]])->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        foreach ($data["rows"] as $key=>$value){
            $data["rows"][$key]["title"]="";
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);

    }

    public function detail(){
        $id=$this->request->request("id",0);
        $detail=$this->model->where(["id"=>$id])->find();
        if(empty($detail)){
            return $this->error("找不到数据");
        }
        $detail->read_count++;
        $detail->show_count++;
        $detail->save();
        return $this->success("成功",$detail);
    }
}
