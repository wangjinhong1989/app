<?php

namespace app\api\controller;


use app\admin\model\Articletype;
use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\admin\model\User;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Guanzhu extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {
        $user = $this->auth->getUser();
        $user_id = $user->id;

        $model = (new \app\admin\model\Guanzhu());
        $lists = $model
            ->with(['user'])
            ->field("guanzhu.id,guanzhu.follow_id,guanzhu.time,user.nickname,user.avatar")
            ->where(['guanzhu.user_id' => $user_id])
            ->where('user.id=guanzhu.follow_id')
            ->select();
        $this->success("成功", $lists);
    }

    /*
    *添加关注
    * **/
    public function add()
    {

        try {
            $data = [];
            $model = new \app\admin\model\Guanzhu();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $follow_id = $this->request->request('follow_id');


            if (!$follow_id) {
                return $this->error(__('参数存在空'));
                die;
            }
            if (!User::getById($follow_id)) {
                return $this->error(__('被关注人'));
            }

            $model->create([
                'user_id' => $user_id, 'follow_id' => $follow_id, 'time' => time()
            ]);

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /*
*删除收藏
* **/
    public function delete()
    {

        try {
            $model = new \app\admin\model\Guanzhu();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $follow_id = $this->request->request('follow_id');


            if (!$follow_id) {
                return $this->error(__('参数存在空'));
            }

            $model->where(['user_id' => $user_id, 'follow_id' => $follow_id])->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

    }

}
