<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function test()
    {
        if(! sendTemplateSMS("17380613281",['1234','5678'])){
            $this->error("发送失败");
        }else
            $this->success("发送成功");

    }


}
