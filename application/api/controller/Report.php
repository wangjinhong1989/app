<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Jubao;
use app\common\controller\Api;
use Complex\Exception;

/**
 * 首页接口
 */
class Report extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new Jubao())->where(['status'=>0])->select();
        $this->success($lists);
    }

    /*
     *添加举报文章.
     * **/
    public function add()
    {

        $data=[];
        $model=new Jubao();

        $user_id=$this->request->param('user_id');
        $type=$this->request->param('type');
        $article_id=$this->request->param('article_id');
        $content=$this->request->param('content');

        if ($user_id && !Validate::is($user_id, "number")) {
            $this->error(__('需要数字'));
        }
        if ($type && !Validate::is($type, "required")) {
            $this->error(__('举报类型必须填写'));
        }

        if ($article_id && !Validate::is($article_id, "number")) {
            $this->error(__('文章ID必须为数字'));
        }
        if ($content && !Validate::is($content, "required")) {
            $this->error(__('内容不能为空'));
        }

        if(Article::getById($article_id)){
            $this->error(__('文章不存在'));
        }

        try{

            $model->create(['user_id'=>$user_id,'article_id'=>$article_id,'content'=>$content,'type'=>$type]);
            $this->success('123',$model->getLastSql());
        }catch (Exception $e){
            $this->error($e->getMessage());
        }

    }
}
