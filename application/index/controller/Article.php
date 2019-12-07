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
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
        $model1=new \app\admin\model\Articletype();
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("TypeList",$model1->where(['id'=>['gt',1]])->select());
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
            $lists=$model->with(['articletype','user'])->where(['user_id'=>$this->auth->getUser()->id])->limit($offset,$limit)->order('id desc')->select();
            $total=$model->with(['articletype','user'])->where(['user_id'=>$this->auth->getUser()->id])->count();
//            $lists = collection($lists)->toArray();
            return json(['total'=>$total,'rows'=>$lists]);
        }else{
            $model=new \app\admin\model\Article();
            $lists=$model->with(['articletype','user'])->where(['user_id'=>$this->auth->getUser()->id])->select();
            $this->view->assign('title', __(''));
            $this->view->assign('lists', $lists);
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
            $data['articletype_ids']=implode(',',$data['articletype_ids']);
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
            $data['articletype_ids']=implode(',',$data['articletype_ids']);
            $data['content']=($data['content']);
            $data['user_id']=$this->auth->getUser()->id;
            $data['create_time']=time();
            $res=$model->save($data,['id'=>$data['id']]);
            $this->success($res,'/index/article/index');

        }else{
            $model=new \app\admin\model\Article();
            $res=$model->where(['id'=>$this->request->param('id')])->find();
            $res['articletype_ids']=explode(',',$res['articletype_ids']);
            $this->view->assign('res',$res);
            return $this->view->fetch();
        }

    }

}
