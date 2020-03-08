<?php

namespace app\admin\model;

use think\Db;
use think\db\Query;
use think\Model;


class Tanchuang extends Model
{

    

    

    // 表名
    protected $name = 'tanchuang';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'url_type_text',
        'status_text',
        'create_time_text',
        'begin_time_text',
        'end_time_text',
    ];
    

    
    public function getUrlTypeList()
    {
        return ['内链' => __('内链'), '外链' => __('外链')];
    }

    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }


    public function getUrlTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['url_type']) ? $data['url_type'] : '');
        $list = $this->getUrlTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getBeginTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['begin_time']) ? $data['begin_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['end_time']) ? $data['end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    protected function setBeginTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function article()
    {
        return $this->belongsTo('Article', 'article_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getOne($notIn){

        $where=$data=[];
        $where["status"]=["eq","显示"];
        $where["begin_time"]=["elt",time()];
        $where["end_time"]=["egt",time()];
        if(!empty($notIn)){
            $lists=self::where($where)->order("paixu","asc")->orderRaw("rand()")->whereNotIn("id",$notIn)->limit(1,1)->select();

            echo self::getLastSql();
        }else {
            $lists=self::where($where)->order("paixu","asc")->orderRaw("rand()")->limit(1,1)->select();
        }

        return $lists;

    }

    public function getNoShow($user_id){

        $where=$data=[];
        $where["tanchuang.status"]=["eq","显示"];
        $where["tanchuang.begin_time"]=["elt",time()];
        $where["tanchuang.end_time"]=["egt",time()];
        $query= Db::table("fa_tanchuang");

        $lists=$query->where($where)->where("tanchuang.id","not in",function ($query,$user_id){
            $exp=[];
            $exp["user_id"]=$user_id;
            $exp["create_time"]=["elt",time()-24*3600];
             $query->table("fa_tanchuang_back")->where($exp)->field("tanchuan_id as id")->select();
        });

        return $lists;

    }

}
