<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Guanggao;
use app\admin\model\HotSearch;
use app\admin\model\Lihaokong;
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
class Likonglihao extends Api
{
    protected $noNeedLogin = ["lists"];
    protected $noNeedRight = ['*'];


    public function update(){

        $article_id=$this->request->request("article_id",0);
        $is_profit=$this->request->request("is_profit","利空");

        $article= (new Article())->where(["id"=>$article_id,"articletype_id"=>2])->find();
        if(!$article){
            return $this->error("找不到快讯");
        }

        if(!($is_profit=="利空"||$is_profit=="利好")){
            return $this->error("参数错误");
        }

        $model=(new Lihaokong());
        $likong= $model->where(["user_id"=>$this->auth->id,"article_id"=>$article_id])->find();

        if($likong){
            $likong->is_profit=$is_profit;
            $likong->save();
        }else {
            $temp=$model->create([
                "user_id"=>$this->auth->id,
                "article_id"=>$article_id,
                "time"=>time(),
                "is_profit"=>$is_profit
            ]);
        }

        return $this->success();

    }

}
