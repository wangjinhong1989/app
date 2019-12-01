<?php

namespace app\api\controller;

use app\admin\model\Jubao;
use app\common\controller\Api;
use Complex\Exception;

/**
 * 首页接口
 */
class Report extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $lists=(new Jubao())->where(['status'=>0])->select();
        $this->success($lists);
    }

    /*
     *添加举报文章.
     * **/
    public function add()
    {

        $data=[];
        $model=new Jubao();

        try{

            $model->create(['user_id'=>$this->request->post('user_id')]);
            $this->success('123',$model->getLastSql());
        }catch (Exception $e){
            $this->error($e->getMessage());
        }

    }
}
