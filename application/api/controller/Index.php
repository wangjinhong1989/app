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
        dd("test");
        $this->success('请求成功');
    }

    public function test()
    {
        $flag=sendTemplateSMS("17380613281",['1234','5678']);
        if($flag){
            $this->success("发送成功");
        }else{
            $this->error("发送失败".$flag);
        }


    }


}
