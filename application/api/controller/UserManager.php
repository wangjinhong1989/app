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
            $where["guanzhu.follow_id"]=["gt",0];

        }else if($follow_id=="未关注"){
            //            $where["guanzhu.follow_id"]=["EXP","IS NULL"];

            $query=new Query();
            $data["rows"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id ")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
                ->limit($offset,$page_size)->order("info.id asc")->group("info.id")->select();

            $data["count"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
                ->group("info.id")->count();

            $data["page"]=$page;
            $data["total_page"]=ceil($data["count"]/$page_size);
            $this->success("成功",$data);

            die;

        }
        $query=new Query();
        $data["rows"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id ")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->limit($offset,$page_size)->order("info.id asc")->group("info.id")->select();

        $data["count"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->group("info.id")->count();

        $data["page"]=$page;
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }


    public static function  re(){
        (new UserManager())->back_lists();
    }

    /**
     * 首页
     *
     */
    public function back_lists()
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

        //  非空.
        $follow_id=$this->request->request("follow_id","");
        if($follow_id=="已关注"){
            $where["guanzhu.follow_id"]=["gt",0];

        }else if($follow_id=="未关注"){
            //            $where["guanzhu.follow_id"]=["EXP","IS NULL"];

            $query=new Query();
            $data["rows"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id ")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
                ->limit($offset,$page_size)->order("info.id asc")->group("info.id")->select();

            $data["count"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
                ->group("info.id")->count();

            $data["page"]=$page;
            $data["total_page"]=ceil($data["count"]/$page_size);
            $this->success("成功",$data);

            die;

        }
        $query=new Query();
        $data["rows"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id ")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->limit($offset,$page_size)->order("info.id asc")->group("info.id")->select();

        foreach ($data["rows"] as $k=>&$value){

            $value["authentication_type"]=null;
            if($value["personal_id"]){
                $value["authentication_type"]="个人";
            }
            if($value["media_id"]){
                $value["authentication_type"]="媒体";
            }
            if($value["enterprise_id"]){
                $value["authentication_type"]="企业";
            }
        }
        $data["count"]=$query->table("user_base_info")->alias("info")->field("info.*,guanzhu.follow_id")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$this->auth->id." and guanzhu.follow_id=info.id" ,"left")
            ->group("info.id")->count();

        $data["page"]=$page;
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }


}
