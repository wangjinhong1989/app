<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 文章类型
 *
 * @icon fa fa-circle-o
 */
class Articletype extends Backend
{
    
    /**
     * Articletype模型对象
     * @var \app\admin\model\Articletype
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Articletype;
        $this->view->assign("showPageList", $this->model->getShowPageList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    /**
     * 查看
     */
    public function kuaixun()
    {

        $where=[];
        $where["id"]=2;
            $total = $this->model
                ->where($where)
                ->count();

            $list = $this->model
                ->where($where)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
    }


    /**
     * 查看
     */
    public function feikuaixun()
    {

        $where=[];
        $total = $this->model
            ->where($where)
            ->count();

        $list = $this->model
            ->where($where)
            ->select();

        $list = collection($list)->toArray();

        $data=array();
        foreach ($list as $k=> &$l){
            if($l["id"]==2||$l["id"]==7||$l["id"]==5){
                unset($list[$k]);
            }else {
                array_push($data,["id"=>$l["id"],"name"=>$l["name"],"pid"=>0]);
            }

        }
        $result = array("total" => count($data), "list" => $data);

        return json($result);
    }


}
