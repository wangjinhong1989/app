<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 企业认证
 *
 * @icon fa fa-circle-o
 */
class AuthenticationEnterprise extends Backend
{
    
    /**
     * AuthenticationEnterprise模型对象
     * @var \app\admin\model\AuthenticationEnterprise
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\AuthenticationEnterprise;
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
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
//                $row->visible(["id",'certificates_type','status','name','certificates_number','files','note','time']);
//                $row->visible(['user']);
//				$row->getRelation('user')->visible(['username']);


            }
            $list = collection($list)->toArray();

            foreach ($list as $key=>$row){
                // 查看运营者审核状态。

                $personal=new \app\admin\model\AuthenticationPersonal();
                $personalInfo= $personal->where(["type"=>"企业认证","user_id"=>$row["user"]["id"]])->select();

                if(!empty($personalInfo)){
                    $list[$key]["peronal_info"]=$personalInfo[0];
                }else {
                    $list[$key]["peronal_info"]=[];
                }

            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
