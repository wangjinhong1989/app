<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\ConfigUser;
use app\admin\model\Guanggao;
use app\admin\model\HotSearch;
use app\admin\model\Lihaokong;
use app\admin\model\PushList;
use app\admin\model\ReadHistory;
use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\Cache;
use think\Db;
use think\db\Query;
use think\view\driver\Think;

/**
 * 首页接口
 */
class ArticleManager extends Api
{
    protected $noNeedLogin = ["lists","recommendation","Lists1","detail"];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",10);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["article.status"]=["eq","显示"];
//        $where["articletype.status"]=["eq","显示"];
        $search=new SearchHistory();
        // 需要查找的类型. 可以设置多个.
        $articletype_id=$this->request->request("articletype_id","");
        if($articletype_id){
            $where["article.articletype_id"]=["in",explode(",",$articletype_id)];
        }

        $where["article.articletype_id"]=["neq",2];
        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["article.title|article.description|article.content"]=["like","%".$keyword."%"];

            if(!empty($this->auth)){
                //  写入关键字检索.
                $history=["user_id"=>$this->auth->id, "word"=>$keyword, "type"=>"标题,描述,内容"];
                $search->save_data($history);
            }
        }


        // 单独查询.
        $title=$this->request->request("title","");
        if($title){
            $where["article.title"]=["like","%".$title."%"];
            if(!empty($this->auth)){
                $history=["user_id"=>$this->auth->id, "word"=>$title, "type"=>"标题"];
                $search->save_data($history);
            }
        }
        $description=$this->request->request("description","");
        if($description){
            $where["article.description"]=["like","%".$description."%"];
            if(!empty($this->auth)) {
                $history = ["user_id" => $this->auth->id, "word" => $description, "type" => "描述"];
                $search->save_data($history);
            }
        }
        $content=$this->request->request("content","");
        if($content){
            $where["article.content"]=["like","%".$content."%"];
            if(!empty($this->auth)){
            $history=["user_id"=>$this->auth->id, "word"=>$content, "type"=>"内容"];
            $search->save_data($history);
            }
        }
        // 查询某个人的文章。
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }

        // 查询某个人的文章。
        $username=$this->request->request("username","");
        if($username){
            $where["user.username"]=["like","%".$username."%"];
        }

        // 查询我关注的人的文章列表.
        $my_follow=$this->request->request("my_follow",'');
        if($my_follow){
            $my_follow=(new \app\admin\model\Guanzhu())->field("follow_id")->where(["user_id"=>$this->auth->id])->select();
            $temp=array();
            foreach ($my_follow as $value){
                $temp[]=$value['follow_id'];
            }
            if(empty($temp)){
                $temp=[0];
            }

            $where["article.user_id"]=["in",$temp];

        }

        $whereExp="";
        $label_ids=$this->request->request("label_ids",'');
        if($label_ids){
            if(!empty($this->auth)) {
                $history = ["user_id" => $this->auth->id, "word" => $label_ids, "type" => "标签"];
                $search->save_data($history);
            }
            $label_ids=explode(",",$label_ids);
            foreach ($label_ids as $k=>$v){

                    if($k!=count($label_ids)-1){
                        $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids) or ';
                    }else {
                        $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids)';
                    }
            }

        }else
            $whereExp=" 1 ";

        // 请求的标签.

        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar,kong_hao.count_lihao,kong_hao.count_likong")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_kong_hao kong_hao","kong_hao.article_id=article.id","left")
            ->limit($offset,$page_size)->order("article.weigh desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->count();


        foreach ($data["rows"] as $key=>&$value){
            $value["create_time"]=formart_time($value["create_time"]);

            $value["count_lihao"]=$value["count_lihao"]==null?0:$value["count_lihao"];
            $value["count_likong"]=$value["count_likong"]==null?0:$value["count_likong"];
            $data["rows"][$key]["count_lihao"]=$data["rows"][$key]["count_lihao"]+$data["rows"][$key]["lh_count"];
            $data["rows"][$key]["count_likong"]=$data["rows"][$key]["count_likong"]+$data["rows"][$key]["lk_count"];
            $value["is_ad"]=false;
        }


        if($data["rows"]){
            if($page==1){
                $need_banner=$this->request->request("need_banner",0);
                if($need_banner){

                    $model=    new Query();
                    $time=time();

                    $lists=$model->table("fa_ad_article")->where(["end_time"=>["egt",$time],"begin_time"=>["elt",$time]])->orderRaw("rand()")->limit(0,1)->select();
                    // 只在头条哪儿展示5个广告.
                    if(!empty($lists)&&$articletype_id==1){
                        $lists[0]["label_ids"]="";
                        $lists[0]["user_id"]="";
                        $lists[0]["articletype_id"]="";
                        $lists[0]["come_from"]="";
                        $lists[0]["articletype_name"]="";
                        $lists[0]["username"]="";
                        $lists[0]["avatar"]="";
                        $lists[0]["is_ad"]=true;
                        $lists[0]["img"]=$lists[0]["images"];
                        $lists[0]["create_time"]=formart_time($lists[0]["begin_time"]);
                        $dataTemp=[];
                        //
                        foreach ($data["rows"] as $key=>$row){
                            array_push($dataTemp,$row);
                            if($key==4){
                                array_push($dataTemp,$lists[0]);
                            }

                        }

                        $data["rows"]=$dataTemp;
                    }


                }
            }
        }

        // 结束.

        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);


    }


    /**
     * 首页
     *
     */
    public function Lists_kuaixun()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",10);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["article.status"]=["eq","显示"];
//        $where["articletype.status"]=["eq","显示"];
        $search=new SearchHistory();
        // 需要查找的类型. 可以设置多个.
        $articletype_id=$this->request->request("articletype_id","");
        if($articletype_id){

        }

        $where["article.articletype_id"]=["eq",2];
        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["article.title|article.description|article.content"]=["like","%".$keyword."%"];

            if(!empty($this->auth)){
                //  写入关键字检索.
                $history=["user_id"=>$this->auth->id, "word"=>$keyword, "type"=>"标题,描述,内容"];
                $search->save_data($history);
            }
        }


        // 单独查询.
        $title=$this->request->request("title","");
        if($title){
            $where["article.title"]=["like","%".$title."%"];
            if(!empty($this->auth)){
                $history=["user_id"=>$this->auth->id, "word"=>$title, "type"=>"标题"];
                $search->save_data($history);
            }
        }
        $description=$this->request->request("description","");
        if($description){
            $where["article.description"]=["like","%".$description."%"];
            if(!empty($this->auth)) {
                $history = ["user_id" => $this->auth->id, "word" => $description, "type" => "描述"];
                $search->save_data($history);
            }
        }
        $content=$this->request->request("content","");
        if($content){
            $where["article.content"]=["like","%".$content."%"];
            if(!empty($this->auth)){
                $history=["user_id"=>$this->auth->id, "word"=>$content, "type"=>"内容"];
                $search->save_data($history);
            }
        }
        // 查询某个人的文章。
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }

        // 查询某个人的文章。
        $username=$this->request->request("username","");
        if($username){
            $where["user.username"]=["like","%".$username."%"];
        }

        // 查询我关注的人的文章列表.
        $my_follow=$this->request->request("my_follow",'');
        if($my_follow){
            $my_follow=(new \app\admin\model\Guanzhu())->field("follow_id")->where(["user_id"=>$this->auth->id])->select();
            $temp=array();
            foreach ($my_follow as $value){
                $temp[]=$value['follow_id'];
            }
            if(empty($temp)){
                $temp=[0];
            }

            $where["article.user_id"]=["in",$temp];

        }

        $whereExp="";
        $label_ids=$this->request->request("label_ids",'');
        if($label_ids){
            if(!empty($this->auth)) {
                $history = ["user_id" => $this->auth->id, "word" => $label_ids, "type" => "标签"];
                $search->save_data($history);
            }
            $label_ids=explode(",",$label_ids);
            foreach ($label_ids as $k=>$v){

                if($k!=count($label_ids)-1){
                    $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids) or ';
                }else {
                    $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids)';
                }
            }

        }else
            $whereExp=" 1 ";

        // 请求的标签.

        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar,kong_hao.count_lihao,kong_hao.count_likong")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_kong_hao kong_hao","kong_hao.article_id=article.id","left")
            ->limit($offset,$page_size)->order("article.weigh desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->count();


        foreach ($data["rows"] as $key=>&$value){
            $value["create_time"]=formart_time($value["create_time"]);

            $value["count_lihao"]=$value["count_lihao"]==null?0:$value["count_lihao"];
            $value["count_likong"]=$value["count_likong"]==null?0:$value["count_likong"];
            $data["rows"][$key]["count_lihao"]=$data["rows"][$key]["count_lihao"]+$data["rows"][$key]["lh_count"];
            $data["rows"][$key]["count_likong"]=$data["rows"][$key]["count_likong"]+$data["rows"][$key]["lk_count"];
            $value["is_ad"]=false;
        }


        if($data["rows"]){
            if($page==1){
                $need_banner=$this->request->request("need_banner",0);
                if($need_banner){

                    $model=    new Query();
                    $time=time();

                    $lists=$model->table("fa_ad_article")->where(["end_time"=>["egt",$time],"begin_time"=>["elt",$time]])->orderRaw("rand()")->limit(0,1)->select();
                    // 只在头条哪儿展示5个广告.
                    if(!empty($lists)&&$articletype_id==1){
                        $lists[0]["label_ids"]="";
                        $lists[0]["user_id"]="";
                        $lists[0]["articletype_id"]="";
                        $lists[0]["come_from"]="";
                        $lists[0]["articletype_name"]="";
                        $lists[0]["username"]="";
                        $lists[0]["avatar"]="";
                        $lists[0]["is_ad"]=true;
                        $lists[0]["img"]=$lists[0]["images"];
                        $lists[0]["create_time"]=formart_time($lists[0]["begin_time"]);
                        $dataTemp=[];
                        //
                        foreach ($data["rows"] as $key=>$row){
                            array_push($dataTemp,$row);
                            if($key==4){
                                array_push($dataTemp,$lists[0]);
                            }

                        }

                        $data["rows"]=$dataTemp;
                    }


                }
            }
        }

        // 结束.

        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);


    }




    public function test(){
        $query=new Query();
        $rows=$query->table("fa_article")->alias("article")->field("article.*")->limit(0,19)->order("id","desc")->select();

            $data=\GuzzleHttp\json_encode($rows);

        var_dump(\GuzzleHttp\json_decode($data,true));

    }

    /**
     * 首页
     *
     */
    public function recommendation()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["article.status"]=["eq","显示"];
        $where["articletype.status"]=["eq","显示"];

        // 需要查找的类型. 可以设置多个.
        $articletype_id=$this->request->request("articletype_id","");
        if($articletype_id){
            $where["article.articletype_id"]=["in",explode(",",$articletype_id)];
        }

        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["article.title|article.description|article.content"]=["like","%".$keyword."%"];
        }


        // 单独查询.
        $title=$this->request->request("title","");
        if($title){
            $where["article.title"]=["like","%".$title."%"];
        }
        $description=$this->request->request("description","");
        if($description){
            $where["article.description"]=["like","%".$description."%"];
        }
        $content=$this->request->request("content","");
        if($content){
            $where["article.content"]=["like","%".$content."%"];
        }
        // 查询某个人的文章。
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }

        // 查询某个人的文章。
        $is_recommendation=$this->request->request("is_recommendation","");
        if($is_recommendation){
            $where["article.is_recommendation"]=["eq",$is_recommendation];
        }

        // 查询某个人的文章。
        $username=$this->request->request("username","");
        if($username){
            $where["user.username"]=["like","%".$username."%"];
        }

        // 查询我关注的人的文章列表.
        $my_follow=$this->request->request("my_follow",'');
        if($my_follow){
            $my_follow=(new \app\admin\model\Guanzhu())->field("follow_id")->where(["user_id"=>$this->auth->id])->select();
            $temp=array();
            foreach ($my_follow as $value){
                $temp[]=$value['follow_id'];
            }
            if(empty($temp)){
                $temp=[0];
            }
            $where["article.user_id"]=["in",$temp];

        }

        $whereExp="";
        $label_ids=$this->request->request("label_ids",'');
        if($label_ids){

            $label_ids=explode(",",$label_ids);
            foreach ($label_ids as $k=>$v){

                if($k!=count($label_ids)-1){
                    $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids) or ';
                }else {
                    $whereExp=$whereExp.'find_in_set('.$v.',article.label_ids)';
                }
            }

        }else
            $whereExp=" 1 ";

        // 请求的标签.

        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar,kong_hao.count_lihao,kong_hao.count_likong")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_kong_hao kong_hao","kong_hao.article_id=article.id","left")
            ->limit($offset,$page_size)->order("article.id desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->count();

        foreach ($data["rows"] as $key=>$v){
            $data["rows"][$key]["create_time"]=formart_time($data["rows"][$key]["create_time"]);
            $data["rows"][$key]["count_lihao"]=$data["rows"][$key]["count_lihao"]+$data["rows"][$key]["lh_count"];
            $data["rows"][$key]["count_likong"]=$data["rows"][$key]["count_likong"]+$data["rows"][$key]["lk_count"];
        }


        // 是否需要返回广告.
        $need_ad=$this->request->request("need_ad",1);
        if($need_ad){
            $guanggao=new Guanggao();
            $ad=Db::table($guanggao->getTable())->where([])->limit(1)->select();
            foreach ($data["rows"] as $key=>$value){
                $data["rows"][$key]["is_ad"]=false;
            }
            if(!empty($ad)){
                $ad[0]["label_ids"]="";
                $ad[0]["user_id"]="";
                $ad[0]["articletype_id"]="";
                $ad[0]["come_from"]="";
                $ad[0]["articletype_name"]="";
                $ad[0]["username"]="";
                $ad[0]["avatar"]="";
                $ad[0]["is_ad"]=true;
                array_push($data["rows"],$ad[0]);
            }
        }
        // 结束.

        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



    /**
     * 包含搜索功能的页面.
     *
     */
    public function Lists1()
    {
        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $model=new Article();
        $data=[];

        $where=[];
        $where["article.status"]=["eq","显示"];

        // 需要查找的类型.
        $articletype_id=$this->request->request("articletype_id",0);
        if($articletype_id){
            $where["article.articletype_id"]=["eq",$articletype_id];
        }

        // keyword 检索. 关键字检索.

        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["article.title|article.description|article.content"]=["like","%".$keyword."%"];
        }


        // 查询某个人的文章。
        $user_id=$this->request->request("user_id","");
        if($user_id){
            $where["article.user_id"]=["eq",$user_id];
        }


        $query=new Query();
        $data["rows"]=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->order("article.id desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->limit($offset,$page_size)->count();


        $guanggao=new Guanggao();

        $ad=Db::table($guanggao->getTable())->where([])->limit(1)->select();

        foreach ($data["rows"] as $key=>$value){
            $data["rows"][$key]["is_ad"]=false;
            $data["rows"][$key]["create_time"]=formart_time($data["rows"][$key]["create_time"]);
        }

        if(!empty($ad)){

            $ad[0]["is_ad"]=true;
            array_push($data["rows"],$ad[0]);

        }
        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



    public function read_history()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];
        $query=new Query();
        $lists= $query->table("fa_read_history")->alias("his")
            ->field("article.*,user.username,user.avatar,articletype.name,his.time")
            ->join("fa_article article","article.id=his.article_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->order("his.time","desc")
            ->limit($offset,$page_size)->select();


        $count= $query->table("fa_read_history")->alias("his")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->count();

        foreach ($lists as &$l){

            $l["create_time"]=date("Y-m-d H:i:s",$l["time"]);
        }


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }



    public function delete_read_history()
    {

        $id=$this->request->request("id",0);

        $model=new ReadHistory();

        if($id>0){
            $model->where(["user_id"=>$this->auth->id,"article_id"=>$id])->delete();
        }else
            $model->where(["user_id"=>$this->auth->id])->delete();


        $this->success("成功","");
    }

    /**
     * 详情页
     *
     */
    public function detail()
    {
        $id=$this->request->request("id",0);
        $model=new Article();

        $where=[];
        $where["article.id"]=$id;

        $query=new Query();
        $detail=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->find();
//        file_put_contents("1",$detail->getLastSql());
        if($detail){

            $detail["content"]=str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-16">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>
<meta name="Generator" content="Cocoa HTML Writer">
<style type="text/css">
p.p1 {margin: 0.0px 0.0px 0.0px 0.0px; font: 16.0px \'.AppleSystemUIFont\'}
span.s1 {font-family: \'.SFUI-Regular\'; font-weight: normal; font-style: normal; font-size: 16.00px}
span.s2 {font-family: \'Helvetica\'; font-weight: normal; font-style: normal; font-size: 12.00px}
</style>
</head>
<body>',"",$detail["content"]);
            $detail["content"]=str_replace('</body></html>',"",$detail["content"]);


            $detail["content"]="<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-16\">
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<title></title>
<meta name=\"Generator\" content=\"Cocoa HTML Writer\">
<style type=\"text/css\">
p.p1 {margin: 0.0px 0.0px 0.0px 0.0px; font: 16.0px '.AppleSystemUIFont'}
span.s1 {font-family: '.SFUI-Regular'; font-weight: normal; font-style: normal; font-size: 16.00px}
span.s2 {font-family: 'Helvetica'; font-weight: normal; font-style: normal; font-size: 12.00px}
</style>
</head>
<body>".$detail["content"]."</body>
</html>";
            $detail["content"]=str_replace('height="310"',"",$detail["content"]);
            $detail["content"]=str_replace('width=\"500\"',"style='width:100%;height:100%;'",$detail["content"]);
            $detail["content"]=str_replace('width="510"',"style='width:100%;height:100%;'",$detail["content"]);
            $detail["content"]=str_replace('width="100%"',"style='width:100%;height:100%;'",$detail["content"]);
            $detail["content"]=str_replace('height="100%"',"",$detail["content"]);
            $article=$model->where(["id"=>$id])->find();
            $article->read_count=$article->read_count+1;
            $article->show_count=$article->show_count+1;
            $article->save();
            $detail['user']=null;

            if($this->auth->id){
                //  增加阅读历史。
                $user_id=$this->auth->id;
                $detail['user']=null;

                // 增加阅读历史记录.
                $his=new ReadHistory();

                $find=$his->where(["user_id"=>$user_id,"article_id"=>$article->id])->find();
                if($find){
                    $detail['user']=$find;
                    $find->time=time();
                    $find->save();
                }else
                    $his->create(["user_id"=>$user_id,"article_id"=>$article->id,"time"=>time()]);
            }

            $label=new \app\admin\model\Label();


            if($detail['label_ids']){
                $detail['label_ids']=$label->where(['id'=>['in',explode(',',$detail['label_ids'])]])->select();
            }else
                $detail['label_ids']=null;


            $detail["auth_enterprise_temp"]=( new \app\admin\model\ZhengjianQiye())->where(["user_id"=>$detail['user_id'],"status"=>"通过"])
                ->find();

            $detail["auth_media_temp"]=( new \app\admin\model\ZhengjianMeiti())->where(["user_id"=>$detail['user_id'],"status"=>"通过"])
                ->find();
            $detail["auth_personal_temp"]=( new \app\admin\model\ZhengjianGeren())->where(["user_id"=>$detail['user_id'],"status"=>"通过"])
                ->find();

            $detail["authentication_type"]="";



            if($detail["auth_personal_temp"]){
                $detail["authentication_type"]="个人";
            }

            if($detail["auth_media_temp"]){
                $detail["authentication_type"]="媒体";
            }

            if($detail["auth_enterprise_temp"]){
                $detail["authentication_type"]="企业";
            }
            // 是否关注了该用户.
            $detail["create_time"]=formart_time($detail["create_time"]);

            if(!empty($this->auth->id)){
                $guanzhu=(new \app\admin\model\Guanzhu())->where(["user_id"=>$user_id,"follow_id"=>$article->user_id])->find();
                if($guanzhu){
                    $detail["is_guanzhu"]="是";
                }else
                    $detail["is_guanzhu"]="否";
            }else {
                $detail["is_guanzhu"]="否";
            }

        }


        $this->success("成功",$detail);
    }

    /*
    *添加文章
    * **/
    public function add()
    {

        $userModel=new \app\admin\model\User();

        $configUser=(new ConfigUser())->where([])->find();

        if(empty($configUser)){
            return $this->error("系统未配置发文配置信息");
        }
        $date=date("Ymd",time());
        $my_number=Cache::get("add_article_number".$this->auth->id.$date);

        if(empty($my_number)) $my_number=0;
        else $my_number=intval($my_number);

        $auth_status=$userModel->auth_status($this->auth->id);

        if($configUser->kaiguan=="关闭"){
            //  不需要处理数量
            $this->fawen($my_number);
        }else{

            if($auth_status==0){
                if($configUser->geren==0){
                    return $this->error("您未认证，不允许发文,今日发文条数".$my_number);
                }else if($configUser->geren<=$my_number){
                    return $this->error("您未认证,发文已经达到".$my_number."条，不允许发文");
                }
                $this->fawen($my_number);
            }

            if($auth_status==3){
                if($configUser->gerencishu==0){
                    return $this->error("【个人认证】的不允许发文,今日发文条数".$my_number);
                }else if($configUser->gerencishu<=$my_number){
                    return $this->error("【个人认证】您已经发文已经达到".$configUser->gerencishu."条，不允许发文");
                }
                $this->fawen($my_number);
            }

            if($auth_status==1){
                if($configUser->qiyecishu==0){
                    return $this->error("【企业认证】的不允许发文,今日发文条数".$my_number);
                }else if($configUser->qiyecishu<=$my_number){
                    return $this->error("【企业认证】您已经发文已经达到".$configUser->qiyecishu."条，不允许发文");
                }
                $this->fawen($my_number);
            }

            if($auth_status==2){
                if($configUser->meiticishu==0){
                    return $this->error("【媒体认证】的不允许发文,今日发文条数".$my_number);
                }else if($configUser->meiticishu<=$my_number){
                    return $this->error("【媒体认证】您已经发文已经达到".$configUser->meiticishu."条，不允许发文");
                }
                $this->fawen($my_number);
            }
        }




    }

    protected function fawen($my_number){
        try{
            $model=new \app\admin\model\Article();
            $user = $this->auth->getUser();
            $user_id = $user->id;

            $data=[];
            // 封面
            $data["img"]=$this->request->request('img','');
            $data["title"]=$this->request->request('title','');
            $data["description"]=$this->request->request('description','');
            $data["content"]=$this->request->request('content','');
            $data["user_id"]=$user_id;
            // 来源地址
            $data["url"]=$this->request->request('url','');

            // 是否允许回复
            $data["is_reply"]=$this->request->request('is_reply','是');
            // 是否原创
            $data["is_mine"]=$this->request->request('is_mine','是');
            // 默认文章类型.  资讯
            $data["articletype_id"]=intval($this->request->request('articletype_id','1'));
            // 标签ID .
            $data["label_ids"]=$this->request->request('label_ids','');
            $data["create_time"]=time();
            // 转载来源.
            $data["come_from"]=$this->request->request('come_from','');

            if(!$data["title"]||!$data["content"]){
                return $this->error(__('标题或者内容为空'));
            }

            $data["content"]=str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-16">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>
<meta name="Generator" content="Cocoa HTML Writer">
<style type="text/css">
p.p1 {margin: 0.0px 0.0px 0.0px 0.0px; font: 16.0px \'.AppleSystemUIFont\'}
span.s1 {font-family: \'.SFUI-Regular\'; font-weight: normal; font-style: normal; font-size: 16.00px}
span.s2 {font-family: \'Helvetica\'; font-weight: normal; font-style: normal; font-size: 12.00px}
</style>
</head>
<body>',"",$data["content"]);
            $data["content"]=str_replace('</body></html>',"",$data["content"]);
            // 这里要求传描述
            if($data["articletype_id"]==2){
                if(!$data["description"]){
                    return $this->error(__('描述不能为空'));
                }
            }else{
                if(!$data["img"]){
                    return $this->error(__('请上传封面'));
                }
            }

            $test=$model->create($data);

            $test->weigh=$test->id;
            $test->save();
            // 保存到推送列表中.

            //dd($test);

            $pushModel=new PushList();

            $temp=[
                "user_id"=>0,
                "push_type_id"=>7,
                "content"=>\GuzzleHttp\json_encode($test),
                "create_time"=>time()
            ];
            $pushModel->create($temp);

            $my_number=$my_number+1;
            Cache::set("add_article_number".$this->auth->id.date("Ymd",time()),$my_number,660);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }
    }

    public function detail_kuaixun()
    {
        $id=$this->request->request("id",0);
        $model=new Article();

        $where=[];
        $where["article.id"]=$id;

        $query=new Query();
        $detail=$query->table("fa_article")->alias("article")->field("article.*,articletype.name as articletype_name,user.username,user.avatar")
            ->where($where)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->find();
        if($detail){

            // 利空利好统计.

            $detail["likong_count"]=(new Lihaokong())->where(["article_id"=>$detail["id"],"is_profit"=>"利空"])->count();
            $detail["lihao_count"]=(new Lihaokong())->where(["article_id"=>$detail["id"],"is_profit"=>"利好"])->count();

            $detail["image"]="http://app.biyouliao.com/uploads/20191224/1c68375a95c34071687ca6a56f5f8933.png";
            //$detail["url"]="http://app.biyouliao.com/uploads/20191224/1c68375a95c34071687ca6a56f5f8933.png";
            //$detail["create_time"]=date("Y-m-d H:i:s",$detail["create_time"]);
            $detail["create_time"]=formart_time($detail["create_time"]);
        }


        $this->success("成功",$detail);
    }


    // 删除。
    public function delete(){

        $model=new Article();
        $id=$this->request->request("id",0);
         $model->where(["id"=>$id,"user_id"=>$this->auth->id])->delete();
        $this->success();
    }

}
