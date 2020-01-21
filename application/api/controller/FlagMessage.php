<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;

/**
 * 首页接口
 */
class FlagMessage extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

   public function detail(){
       $model=new \app\admin\model\FlagMessage();
       $info=$model->where(["user_id"=>$this->auth->id])->find();
       return $this->success("成功",$info);
   }

   // 点击后 设置为 0
   public function update(){
       // 为作者添加评论
       $flag=(new \app\admin\model\FlagMessage())->where(["user_id"=>$this->auth->id])->find();

       $param=$this->request->request("param","");
       if($param=="comment_flag"){
           $flag->comment_flag=0;
       }else if($param=="reply_flag"){
           $flag->comment_flag=0;
       }

       $flag->save()
   }
}
