<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\Articletype;
use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;

/**
 * 首页接口
 */
class Data extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //  添加用户
    public function  user(){

        for ($i=100;$i<200;$i++){
            $mobile="13800000".$i;
            $ret = $this->auth->register("username_".$i, "123456", '', $mobile, []);
        }
    }

    // 根据分类添加各类文章.
    public function article(){
            $type=new Articletype();
            $types=$type->where([])->select();
            $types=collection($types)->toArray();
            $user=(new \app\admin\model\User())->where([])->select();
            $user=collection($user)->toArray();
            $labels=(new \app\admin\model\Label())->where([])->select();
            $labels=collection($labels)->toArray();
            $article= new Article();


            for ($i=0;$i<100;$i++){
            $time=time();
            $article->create(["title"=>"title".$time,
                "description"=>" description".$time,
                "img"=>"http://app.luxiaogui.cn//uploads/20191230/8f4c7653f9b9333617a2664960298ddf.jpg",
                "content"=>" content".$time,
                "is_mine"=>"是",
                "is_reply"=>"是",
                "create_time"=>$time,
                'read_count'=>rand(0,1000),
                'show_count'=>rand(0,1000),
                'articletype_id'=>$types[rand(0,count($types)-1)]['id'],
                'label_ids'=>$labels[rand(0,count($labels)-1)]['id'].",".$labels[rand(0,count($labels)-1)]['id'],
                'user_id'=>$user[rand(0,count($user)-1)]['id']
            ]);
            }
    }

    // 根据分类添加各类文章.
    public function subject(){
        $model=new \app\admin\model\Subject();

        for ($i=0;$i<100;$i++){
            $time=time();
            $model->create(["title"=>"title".rand(0,1000)."_".rand(0,1000),
                "description"=>" description".$time,
                "image"=>"http://app.luxiaogui.cn//uploads/20191230/8f4c7653f9b9333617a2664960298ddf.jpg",
                "content"=>" content".$time,
                'weigh'=>0,
                "create_time"=>$time,
                'read_count'=>rand(0,1000),
            ]);
        }
    }

}
