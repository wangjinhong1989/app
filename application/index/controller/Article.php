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
            $lists=$model->with(['articletype','user'])->where(['user_id'=>$this->auth->getUser()->id])->select();
//            $lists = collection($lists)->toArray();
            return json(['total'=>1,'rows'=>$lists]);
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
            $data['content']=($data['content']);
            $data['user_id']=$this->auth->getUser()->id;
            $data['create_time']=time();
            $res=$model->data($data)->save();
            $this->success($res,$model->getLastSql());
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
            $lists=$model->with(['articletype','user'])->where(['user_id'=>$this->auth->getUser()->id])->select();
//            $lists = collection($lists)->toArray();
            return json(['total'=>1,'rows'=>$lists]);
        }else{
            return $this->view->fetch();
        }

    }

}
