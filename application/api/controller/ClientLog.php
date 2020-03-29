<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Guanggao;
use think\Cache;
use think\Config;
use think\db\Query;

/**
 * 首页接口
 */
class ClientLog extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function  upload(){
        $content=$this->request->request("content","");
        $type=$this->request->request("type","错误日志");
        $user_id=$this->request->request("user_id",0);

        $model=new \app\admin\model\ClientLog();

        $model->create([
            "content"=>$content,
            "type"=>$type,
            "user_id"=>$user_id,
            "create_time"=>time()

        ]);

        return $this->success();
    }
}
