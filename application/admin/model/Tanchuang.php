<?php

namespace app\admin\model;

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
        'create_time_text'
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


    public function article()
    {
        return $this->belongsTo('Article', 'article_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}