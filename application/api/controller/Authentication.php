<?php

namespace app\api\controller;
use app\common\controller\Api;
/**
 * 首页接口
 */
class Authentication extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $authentication_type=$this->request->post("authentication_type");
        $lists=( new \app\admin\model\Authentication())
            ->with("certificates")
            ->where(['authentication_type'=>$authentication_type])
            ->where('certificates.id=authentication.certificates_id')
            ->select();
        $this->success($lists);
    }

}
