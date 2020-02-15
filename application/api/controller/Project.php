<?php

namespace app\api\controller;

use app\admin\model\Shoucang;
use app\admin\model\Article;
use app\common\controller\Api;

/**
 * 首页接口
 */
class Project extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function Lists()
    {

        $page=$this->request->request("page",1);
        $page_size=$this->request->request("page_size",5);

        $offset=($page-1)*$page_size;

        $where=[];
        $where["status"]=["eq","显示"];
        $keyword=$this->request->request("keyword","");
        if($keyword){
            $where["name"]=["like","%".$keyword."%"];
        }
        $data=[];

        $data["rows"]=(new \app\admin\model\Project())->where($where)->limit($offset,$page_size)->order("hot","desc")->select();
        $data["count"]=(new \app\admin\model\Project())->where($where)->count();

        foreach ($data["rows"] as $k=>&$v){
            $v["rank"]=($data["count"]-(($page-1)*$page_size-1)-$k);
        }
        $data["page"]=$page;

        $data["total_page"]=ceil($data["count"]/$page_size);
        $this->success("成功",$data);
    }

    /*
    *更新项目阅读
    * **/
    public function detail()
    {

        try{
            $model=new \app\admin\model\Project();
            $id=$this->request->request('id');

            if(!$id){
                return $this->error(__('参数存在空'));
            }
            if(!$model::getById($id)){
                return $this->error(__('项目不存在'));
            }
            $info=$model::getById($id);
            $info->hot=$info->hot+1;

 //          $info->rank=$info->rank+1;
//           $model->save(['hot'=>$info->hot,'rank'=>$info->rank],['id'=>$id]);
            $info->save();
            return $this->success("成功",$info);
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }


    /*
*更新项目阅读
* **/
    public function update()
    {

        try{
            $model=new \app\admin\model\Project();
            $id=$this->request->request('id');

            if(!$id){
                return $this->error(__('参数存在空'));
            }
            if(!$model::getById($id)){
                return $this->error(__('项目不存在'));
            }
            $info=$model::getById($id);
            $info->hot=$info->hot+1;
            $info->rank=$info->rank+1;
            $info->save();
            return $this->success("成功");
        }catch (Exception $e){
            return  $this->error($e->getMessage());
        }

    }

}
