<?php

namespace app\admin\command;

use app\admin\command\Api\library\Builder;
use app\admin\model\SystemMessage;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;


use app\admin\model\Article;
use app\admin\model\PushList;
use app\admin\model\PushType;
use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\db\Query;
use think\Hook;

class Top extends Command
{
    protected function configure()
    {
        $site = Config::get('site');
        $this
            ->setName('top')
            ->addOption('top', 't', Option::VALUE_OPTIONAL, 'default push url', '')
            ->setDescription('J Push');
    }

    protected function execute(Input $input, Output $output)
    {
        set_time_limit(0);

        $query = new Query();

//        $query->table("fa_article")->where(["begin_time"=>["lt",time()],"top"=>["eq","置顶"]])->whereOr(["end_time"=>["gt",time()],"top"=>["eq","置顶"]])->chunk(100, function ($list) {
//
//            // 需要推送的列表.
//            foreach ($list as  $l){
//
//                $model=(new Article())->where(["id"=>$l["id"]])->find();
//                $model->weigh=$l["id"];
//                $model->top="取消置顶";
//                $model->save();
//            }
//        });

        $query->table("fa_article")->where(["weigh"=>["eq",0]])->chunk(100, function ($list) {

            // 需要推送的列表.
            foreach ($list as  $l){

                $model=(new Article())->where(["id"=>$l["id"]])->find();

                if($model->top=="置顶"){
                    $model->weigh=time();
                }else{
                    $model->weigh=$l["id"];
                }

                $model->save();
            }
        });
    }


}
