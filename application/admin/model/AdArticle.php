<?php

namespace app\admin\model;

use think\Model;


class AdArticle extends Model
{

    

    

    // 表名
    protected $name = 'ad_article';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'url_type_text',
        'begin_time_text',
        'end_time_text'
    ];
    

    
    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }

    public function getUrlTypeList()
    {
        return ['外链' => __('外链'), '内链' => __('内链')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getUrlTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['url_type']) ? $data['url_type'] : '');
        $list = $this->getUrlTypeList();
        return isset($list[$value]) ? $list[$value] : '';
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
        return $this->belongsTo('Article', 'title', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
