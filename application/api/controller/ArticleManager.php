<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
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
        $data["rows"]=$model->where(['status'=>0])->limit($offset,$page_size)->select();
        $data["count"]=$model->where(['status'=>0])->count();
        $data["page"]=$page;
        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

}
