<?php

namespace app\admin\model;

use think\Model;


class Articletype extends Model
{

    

    

    // 表名
    protected $name = 'articletype';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'show_page_text',
        'status_text',
        'create_time_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getShowPageList()
    {
        return ['全部页面' => __('全部页面'), '文章页面' => __('文章页面'), '快讯页面' => __('快讯页面'), '其他页面' => __('其他页面')];
    }

    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }


    public function getShowPageTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['show_page']) ? $data['show_page'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getShowPageList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
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

    protected function setShowPageAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
