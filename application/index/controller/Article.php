<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Sms;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 */
class Article extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['share','share_kuaixun'];
    protected $noNeedRight = ['*'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
        $model1=new \app\admin\model\Articletype();
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("TypeList",$model1->where(['id'=>['gt',1],"status"=>"显示"])->select());
    }
        /**
     * 文章列表
     */
    public function index()
    {
        if($this->request->isAjax()){
            $model=new \app\admin\model\Article();
            $offset=$this->request->get('offset',1);
            $limit=$this->request->get('limit',10);
            $lists=$model->alias('article')->with(['articletype','user'])->where(['article.user_id'=>$this->auth->getUser()->id])->limit($offset,$limit)->order('id desc')->select();
            $total=$model->alias('article')->with(['articletype','user'])->where(['article.user_id'=>$this->auth->getUser()->id])->count();
//            $lists = collection($lists)->toArray();
            return json(['total'=>$total,'rows'=>$lists,$model->getLastSql()]);
        }else{
            $this->view->assign('title', __(''));
            return $this->view->fetch();
        }

    }



    /**
     * add
     */
    public function add()
    {
        if($this->request->isPost()){
            $model=new \app\admin\model\Article();
            $data=$params = $_REQUEST['row'];

            $data['content']=($data['content']);

            $data['user_id']=$this->auth->getUser()->id;
            $data['create_time']=time();
            $res=$model->data($data)->save();
            $this->success($res,'/index/article/index');
        }else{
            return $this->view->fetch();
        }

    }


    /**
     * 文章列表
     */
    public function edit()
    {
        if($this->request->isAjax()){

            $model=new \app\admin\model\Article();
            $data=$params = $_REQUEST['row'];
            $data['content']=($data['content']);
            $data['user_id']=$this->auth->getUser()->id;
            $data['create_time']=time();
            $data['is_reply']="是";
            $data['status']="显示";
            $res=$model->save($data,['id'=>$data['id']]);
            $this->success($res,'/index/article/index');

        }else{
            $model=new \app\admin\model\Article();
            $res=$model->where(['id'=>$this->request->param('id')])->find();
            $res['articletype_id']=explode(',',$res['articletype_id']);
            $this->view->assign('res',$res);
            return $this->view->fetch();
        }

    }

    /**
     * 文章列表
     */
    public function delete()
    {

            $id=$this->request->get('id',0);
            $model=\app\admin\model\Article::get($id);


        $model->status="隐藏";
        $model->save();

            $this->redirect('/index/article');

    }

    // 分享到的详情页面。
    public function  share(){


            $model=new \app\admin\model\Article();
            $res=$model->where(['id'=>$this->request->param('id')])->find();
            //$res['articletype_ids']=explode(',',$res['articletype_ids']);
            $this->view->assign('res',$res);
            return $this->view->fetch();

        //var_dump($res);

    }

    // 分享到的详情页面。
    public function  share_kuaixun(){


        $model=new \app\admin\model\Article();
        $res=$model->where(['id'=>$this->request->param('id')])->find();
        //$res['articletype_ids']=explode(',',$res['articletype_ids']);
        $this->view->assign('res',$res);
        return $this->view->fetch();

        //var_dump($res);

    }


}
