<?php

namespace app\api\controller;


use app\admin\model\SearchHistory;
use app\common\controller\Api;
use think\db\Query;

/**
 * 首页接口
 */
class Mylabel extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 热搜
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);
        $offset=($page-1)*$page_size;
        $data=[];

        $query=new Query();
        $lists=$query->table("fa_mylabel")->alias("mylabel")->field("mylabel.*,label.name")
            ->where(["mylabel.user_id"=>['eq',$this->auth->id],'label.id'=>['eq','mylable.label_id']])
            ->join("fa_label label","label.id=mylabel.label_id","left")
            ->limit($offset,$page_size)->order("mylabel.id desc")->select();

        $count=$query->table("fa_mylabel")->alias("mylabel")->field("mylabel.*,label.name")
            ->where(["mylabel.user_id"=>['eq',$this->auth->id],'label.id'=>['eq','mylable.label_id']])
            ->join("fa_label label","label.id=mylabel.label_id","left")
            ->count();

//        $lists=( new \app\admin\model\Mylabel())->with("label")->where(['mylabel.user_id'=>$this->auth->getUser()->id])->limit($offset,$page_size)->select();
//        $count=( new \app\admin\model\Mylabel())->where(['user_id'=>$this->auth->getUser()->id])->count();


        $data["page"]=$page;
        $data["rows"]=$lists;
        $data["count"]=$count;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }




    /*
    *添加收藏
    * **/
    public function add()
    {

        try{
            $data=[];
            $model=new \app\admin\model\Mylabel();
            $user = $this->auth->getUser();
            $user_id = $user->id;
            $label_id=$this->request->request('label_id');

            if(!$label_id){
                return $this->error(__('参数存在空'));
            }

            if($model->where(['user_id'=>$user_id,'label_id'=>$label_id])->find()){
                return $this->error(__('已经添加了，请不要重复添加'));
            }
            $model->create([
                'user_id'=>$user_id,'label_id'=>$label_id,'time'=>time()
            ]);

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


    /*
*清除收藏
* **/
    public function delete()
    {

        try{
            $model=new \app\admin\model\Mylabel();
            $user = $this->auth->getUser();
            $user_id=$user->id;

            $label_id=$this->request->request('label_id',0);

            $model->where(['user_id'=>$user_id,'label_id'=>$label_id])->delete();

            return $this->success();
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }
}
