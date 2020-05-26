<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\db\Query;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $this->view->assign([
            'totaluser'        => 35200,
            'totalviews'       => 219390,
            'totalorder'       => 32143,
            'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);


        //总用户数
        $user_total= (new Query())->table("fa_user")->where([])->count();

        $article_total= (new Query())->table("fa_article")->where([])->count();


        $time=date("Y-m-d 00:00:00",time());
        $start_time=strtotime($time);
        $end_time=strtotime($time)+24*3600;

        $where=[];
        $where["create_time"]=[
            ["gt",$start_time],
            ["lt",$end_time]
        ];
        $where1=[];
        $where1["createtime"]=[
            ["gt",$start_time],
            ["lt",$end_time]
        ];
        $article_total_today= (new Query())->table("fa_article")->where($where)->count();


        $user_total_today= (new Query())->table("fa_user")->where($where1)->count();

        $user_login_total_today= (new Query())->table("fa_user")->where(["updatetime"=>[
            ["gt",$start_time],
            ["lt",$end_time]
        ]])->count();

        $jubao_total_today= (new Query())->table("fa_jubao")->where(["time"=>[
            ["gt",$start_time],
            ["lt",$end_time]
        ]])->count();

        $jubao_total= (new Query())->table("fa_jubao")->where(["time"=>[
            ["gt",$start_time],
            ["lt",$end_time]
        ]])->count();

        $youke_total_today= (new Query())->table("fa_vistor_log")->where(["open_time"=>[
            ["gt",date("Y-m-d 00:00:00")],
            ["lt",date("Y-m-d 23:59:59")]
        ],"user_id"=>0])->distinct("IP")->count();

        $yingdao_total_today=Db::query('SELECT count(DISTINCT IP) FROM `fa_vistor_log` WHERE open_time< '.date("Y-m-d").'"23:59:59" AND open_time> "'.date("Y-m-d").' 00:00:00" and page="引导页"');

//            $query->table("fa_vistor_log")->where(["open_time"=>[
//            ["gt",date("Y-m-d 00:00:00")],
//            ["lt",date("Y-m-d 23:59:59")]
//        ],"user_id"=>0,"page"=>"引导页"])->distinct("IP")->count();

        $query=new Query();
        $zhuye_total_today= $query->table("fa_vistor_log")->where(["open_time"=>[
            ["gt",date("Y-m-d 00:00:00")],
            ["lt",date("Y-m-d 23:59:59")]
        ],"user_id"=>0,"page"=>"主页列表"])->distinct("IP")->count();
//        echo $query->getLastSql();

        $this->view->assign('user_total', $user_total+Config::get("site.用户总数"));
        $this->view->assign('user_total_today', $user_total_today+Config::get("site.新注册数"));
        $this->view->assign('article_total', $article_total+Config::get("site.总发帖数"));
        $this->view->assign('article_total_today', $article_total_today+Config::get("site.今日发帖数"));
        $this->view->assign('user_login_total_today', $user_login_total_today+Config::get("site.今日登陆数"));
        $this->view->assign('jubao_total_today', $jubao_total_today+Config::get("site.总举报数"));
        $this->view->assign('jubao_total', $jubao_total+Config::get("site.今日举报数"));
        $this->view->assign('jubao_total', $jubao_total+Config::get("site.今日举报数"));
        $this->view->assign('youke_total', $jubao_total+Config::get("site.今日举报数"));
        $this->view->assign('youke_total_today', $youke_total_today);
        $this->view->assign('yingdao_total_today', $yingdao_total_today);
        $this->view->assign('zhuye_total_today', $zhuye_total_today);


        return $this->view->fetch();
    }

}
