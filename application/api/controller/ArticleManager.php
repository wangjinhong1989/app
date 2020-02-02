<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Guanggao;
use app\admin\model\HotSearch;
use app\admin\model\PushList;
use app\admin\model\ReadHistory;
use app\admin\model\SearchHistory;
use app\common\controller\Api;
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
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;

        if($offset<0){
            $offset=0;
        }
        $data=[];
        $where=[];
        $where["article.status"]=["eq","显示"];
        $where["articletype.status"]=["eq","显示"];
        $search=new SearchHistory();
        // 需要查找的类型. 可以设置多个.
        $articletype_id=$this->request->request("articletype_id","");
        if($articletype_id){
            $where["article.articletype_id"]=["in",explode(",",$articletype_id)];
        }

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
            ->limit($offset,$page_size)->order("article.id desc")->select();



        $data["count"]=$query->table("fa_article")->alias("article")
            ->where($where)
            ->whereExp('',$whereExp)
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->count();


        foreach ($data["rows"] as &$value){
            $value["content"]=html_entity_decode($value["content"]);
            $value["create_time"]=date("Y-m-d H:i:s",$value["create_time"]);
        }


        // 是否需要返回广告.
        $need_ad=$this->request->request("need_ad",1);
        if($need_ad){
            $model=    new Query();
            // more
            $ad_size=$this->request->request("ad_size",1);
            $ad=$lists=$model->table("fa_guanggao")->where(['status'=>'显示'])->orderRaw("rand()")->limit(0,$ad_size)->select();
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
                $ad[0]["create_time"]=date("Y-m-d H:i:s",$ad[0]["create_time"]);
                array_push($data["rows"],$ad[0]);
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
            ->field("article.*,user.username,user.avatar,articletype.name")
            ->join("fa_article article","article.id=his.article_id","left")
            ->join("fa_user user","user.id=article.user_id","left")
            ->join("fa_articletype articletype","articletype.id=article.articletype_id","left")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->limit($offset,$page_size)->select();


        $count= $query->table("fa_read_history")->alias("his")
            ->where(['his.user_id'=>$this->auth->getUser()->id])
            ->count();



        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
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
            $article=$model->where(["id"=>$id])->find();
            $article->read_count=$article->read_count+1;
            $article->show_count=$article->show_count+1;
            $article->save();

            //  增加阅读历史。
            $user_id=$this->auth->id;
            $detail['user']=null;
            if($user_id!=$article->user_id){
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


            $detail["企业认证"]=( new \app\admin\model\AuthenticationEnterprise())->where(["user_id"=>$detail['user_id']])
                ->find();

            $detail["媒体认证"]=( new \app\admin\model\AuthenticationMedia())->where(["user_id"=>$detail['user_id']])
                ->find();
            $detail["个人认证"]=( new \app\admin\model\AuthenticationPersonal())->where(["user_id"=>$detail['user_id']])
                ->find();

            $detail["authentication_type"]="";



            if($detail["个人认证"]){
                $detail["authentication_type"]="个人";
            }

            if($detail["媒体认证"]){
                $detail["authentication_type"]="媒体";
            }

            if($detail["企业认证"]){
                $detail["authentication_type"]="企业";
            }
            // 是否关注了该用户.

            $guanzhu=(new \app\admin\model\Guanzhu())->where(["user_id"=>$user_id,"follow_id"=>$article->user_id])->find();
            if($guanzhu){
                $detail["is_guanzhu"]="是";
            }else
                $detail["is_guanzhu"]="否";
        }


        $this->success("成功",$detail);
    }

    /*
    *添加文章
    * **/
    public function add()
    {

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

            //$data["content"]=($data["content"]);
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

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


}
