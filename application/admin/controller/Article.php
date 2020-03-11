<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Article extends Backend
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
        $this->view->assign("isTopList", $this->model->getTopList());
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams("title,user.username,id");


            if($this->request->get("n_kuaixun")){
                $total = $this->model
                    ->with(['articletype','label','user'])
                    ->where($where)
                    ->where(["article.articletype_id"=>["neq",2]])
                    ->order($sort, $order)
                    ->count();

                $list = $this->model
                    ->with(['articletype','label','user'])
                    ->where($where)
                    ->where(["article.articletype_id"=>["neq",2]])
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            }else {
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
            }


            foreach ($list as $row) {
                $row->visible(['id','title','description','status','come_from',"top",'label_ids','url','img','read_count','show_count']);
                $row->visible(['articletype']);
				$row->getRelation('articletype')->visible(['name']);
				$row->visible(['label']);
				$row->getRelation('label')->visible(['name']);
				$row->visible(['user']);
				$row->getRelation('user')->visible(['username']);

				if($row->top=="取消置顶"){
                    $row->top="无";
                }
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);


            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                if($params["top"]=="置顶"||$params["top"]=="推广"||$params["top"]=="广告"){
                    if(empty($params["begin_time"])||empty($params["end_time"])){
                        $this->error("请填写置顶时间");
                    }
                    if(strtotime($params["begin_time"])>=strtotime($params["end_time"])){
                        $this->error("置顶时间开始时间大于等于结束时间");
                    }
                }

                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $result = $this->model->allowField(true)->save($params);

                    if($params["top"]=="置顶"||$params["top"]=="推广"||$params["top"]=="广告"){
                        if(strtotime($params["begin_time"])<=time()&&strtotime($params["end_time"])>=time())
                            $params["weigh"]=time();
                        else
                            $params["weigh"]=$this->model->getLastInsID();
                    }else {
                        $params["weigh"]=$this->model->getLastInsID();
                    }
                    $params["id"]=$this->model->getLastInsID();
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }


        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {

                if($params["top"]=="置顶"||$params["top"]=="推广"||$params["top"]=="广告"){
                    if(empty($params["begin_time"])||empty($params["end_time"])){
                        $this->error("请填写置顶时间");
                    }
                    if(strtotime($params["begin_time"])>=strtotime($params["end_time"])){
                        $this->error("置顶时间开始时间大于等于结束时间");
                    }
                }

                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }

                    if($params["top"]=="置顶"||$params["top"]=="推广"||$params["top"]=="广告"){
                        if(strtotime($params["begin_time"])<=time()&&strtotime($params["end_time"])>=time())
                        $params["weigh"]=time();
                        else
                            $params["weigh"]=$ids;

                    }else {
                        $params["weigh"]=$ids;
                    }

                    $result = $row->allowField(true)->save($params);

                    Db::commit();
                    dd($result);
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }




}
