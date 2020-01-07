<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Guanggao;
use app\admin\model\ReadHistory;
use app\common\controller\Api;
use think\Db;
use think\db\Query;
use think\view\driver\Think;

/**
 * 首页接口
 */
class UserManager extends Api
{
    protected $noNeedLogin = [];
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

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        // 不是自己.
        $where["info.id"]=['<>',$this->auth->id];
        $username=$this->request->request("username","");
        if($username){
            $where["info.username"]=['like',"%".$username."%"];

        }

        //  非空.
        $follow_id=$this->request->request("follow_id","");
        if($follow_id=="已关注"){
            $where["guanzhu.follow_id"]="not null";

        }else if($follow_id=="未关注"){
            $where["guanzhu.follow_id"]=null;
        }

        $query=new Query();
        $data["rows"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id ")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->limit($offset,$page_size)->order("info.id asc")->group("info.id")->select();

        echo $query->getLastSql();
        $data["count"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->group("info.id")->count();

        $data["page"]=$page;
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



}
