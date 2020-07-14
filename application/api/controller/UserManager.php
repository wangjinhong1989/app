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
    protected $noNeedLogin = ["*"];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",20);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        // 不是自己.

        $my_id=0;
        if($this->auth->id){
            $my_id=$this->auth->id;
        }
        $where["info.id"]=['<>',$my_id];
        $username=$this->request->request("username","");
        if($username){
            $where["info.username"]=['like',"%".$username."%"];

        }

        //  非空.
        $follow_id=$this->request->request("follow_id","未关注");
        if($follow_id=="已关注"){
            //            $where["guanzhu.follow_id"]=["EXP","IS NULL"];

            $query=new Query();
            $data["rows"]=$query->table("fa_user")->alias("info")->field("info.id,info.username,info.nickname,info.mobile,info.avatar,info.level,info.gender,info.birthday,info.bio,guanzhu.follow_id ")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$my_id." and guanzhu.follow_id=info.id" ,"left")
                ->limit($offset,$page_size)->order("info.id desc")->group("info.id")->select();

            $data["count"]=$query->table("fa_user")->alias("info")->field("info.id,guanzhu.follow_id")
                ->where($where)
                ->whereNull("guanzhu.follow_id")
                ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$my_id." and guanzhu.follow_id=info.id" ,"left")
                ->group("info.id")->count();
            foreach ($data["rows"] as $k=>&$value){

                $data["rows"][$k]["my_follow"]=0;
                $data["rows"][$k]["follow_me"]=0;
                $data["rows"][$k]["count_my_article"]=0;
                $data["rows"][$k]["personal_id"]=0;
                $data["rows"][$k]["personal_status"]="审核中";
                $data["rows"][$k]["enterprise_id"]=0;
                $data["rows"][$k]["enterprise_status"]="";
                $data["rows"][$k]["media_id"]=0;
                $data["rows"][$k]["media_status"]="";
                $data["rows"][$k]["authentication_type"]="";
                if($value["personal_id"]){
                    $data["rows"][$k]["authentication_type"]="个人";
                }
                if($value["media_id"]){
                    $data["rows"][$k]["authentication_type"]="媒体";
                }
                if($value["enterprise_id"]){
                    $data["rows"][$k]["authentication_type"]="企业";
                }
            }
            $data["page"]=$page;
            $data["total_page"]=ceil($data["count"]/$page_size);
            $this->success("成功",$data);

            die;

        }
        $query=new Query();
        $data["rows"]=$query->table("fa_user")->alias("info")->field("info.*,guanzhu.follow_id ")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$my_id." and guanzhu.follow_id=info.id" ,"left")
            ->limit($offset,$page_size)->order("info.id desc")->group("info.id")->select();

        $data["count"]=$query->table("fa_user")->alias("info")->field("info.*,guanzhu.follow_id")
            ->where($where)
            ->join("fa_guanzhu guanzhu","guanzhu.user_id= ".$my_id." and guanzhu.follow_id=info.id" ,"left")
            ->group("info.id")->count();
        foreach ($data["rows"] as $k=>&$value){

            $data["rows"][$k]["authentication_type"]="";
            if($value["personal_id"]){
                $data["rows"][$k]["authentication_type"]="个人";
            }
            if($value["media_id"]){
                $data["rows"][$k]["authentication_type"]="媒体";
            }
            if($value["enterprise_id"]){
                $data["rows"][$k]["authentication_type"]="企业";
            }
        }

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
        $page_size=100;//$this->request->request("page_size",100);
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
            foreach ($data["rows"] as $k=>&$value){

                $data["rows"][$k]["authentication_type"]=null;
                if($value["personal_id"]){
                    $data["rows"][$k]["authentication_type"]="个人";
                }
                if($value["media_id"]){
                    $data["rows"][$k]["authentication_type"]="媒体";
                }
                if($value["enterprise_id"]){
                    $data["rows"][$k]["authentication_type"]="企业";
                }
            }
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

            $data["rows"][$k]["authentication_type"]=null;
            if($value["personal_id"]){
                $data["rows"][$k]["authentication_type"]="个人";
            }
            if($value["media_id"]){
                $data["rows"][$k]["authentication_type"]="媒体";
            }
            if($value["enterprise_id"]){
                $data["rows"][$k]["authentication_type"]="企业";
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
