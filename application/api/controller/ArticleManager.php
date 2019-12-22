<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
use think\Db;
use think\view\driver\Think;

/**
 * 首页接口
 */
class ArticleManager extends Api
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
        $model=new Article();
        $data=[];
        $data["rows"]=$model->with("user,articletype")->where(['article.status'=>"显示"])->limit($offset,$page_size)->select();
        $data["count"]=$model->where(['status'=>0])->count();

        $data["page"]=$page;

        foreach ($data["rows"] as $key=>$value){
            $data["rows"][$key]["username"]=$data["rows"][$key]["user"]["username"];
            $data["rows"][$key]["avatar"]=$data["rows"][$key]["user"]["avatar"];
            $data["rows"][$key]["articletype_name"]=$data["rows"][$key]["articletype"]["name"];
            unset($data["rows"][$key]["user"]);
            unset($data["rows"][$key]["articletype"]);
        }
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
