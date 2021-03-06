<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Jubao;
use app\common\controller\Api;
use Complex\Exception;
use think\Validate;
/**
 * 首页接口
 */
class Report extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $user = $this->auth->getUser();
        $user_id=$user->id;
        $lists=(new Jubao())->with(['article'])
            ->where("jubao.article_id=article.id")
            ->where(['jubao.user_id'=>$user_id])->select();
        $this->success("成功",$lists);
    }

    /*
     *添加举报文章.
     * **/
    public function add()
    {

        try{
        $data=[];
        $model=new Jubao();
        $user = $this->auth->getUser();
        $user_id=$user->id;
        $type=$this->request->request('type');
        $article_id=$this->request->request('article_id');
        $content=$this->request->request('content');

        if(!$user_id||!$type||!$article_id||!$content){
           return $this->error(__('参数存在空'));
        }
        if ($user_id && !Validate::is($user_id, "number")) {
            return $this->error(__('需要数字'));
        }
        if ($type && !Validate::is($type, "require")) {
            return $this->error(__('举报类型必须填写 |'.$type."|"));
        }

        if ($article_id && !Validate::is($article_id, "number")) {
            return $this->error(__('文章ID必须为数字'));
        }
        if ($content && !Validate::is($content, "require")) {
            return $this->error(__('内容不能为空'));
        }

        if(!Article::getById($article_id)){
            return $this->error(__('文章不存在'));
        }

            $model->create([
                'user_id'=>$user_id,'article_id'=>$article_id,'content'=>$content,'type'=>$type,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }
}
