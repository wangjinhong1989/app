<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ArticleKuaixun extends Backend
{
    
    /**
     * Article模型对象
     * @var \app\admin\model\Article
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("isReplyList", $this->model->getIsReplyList());
        $this->view->assign("isMineList", $this->model->getIsMineList());
        $this->view->assign("isRecommendationList", $this->model->getIsRecommendationList());
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
            //$where["articletype_id"]=2;
            $total = $this->model
                    ->with(['articletype','label','user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['articletype','label','user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','title','description','status','come_from','label_ids','url','img','read_count','show_count']);
                $row->visible(['articletype']);
				$row->getRelation('articletype')->visible(['name']);
				$row->visible(['label']);
				$row->getRelation('label')->visible(['name']);
				$row->visible(['user']);
				$row->getRelation('user')->visible(['username']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


}