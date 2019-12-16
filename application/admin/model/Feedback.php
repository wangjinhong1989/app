<?php

namespace app\admin\model;

use think\Model;


class Feedback extends Model
{

    

    

    // 表名
    protected $name = 'feedback';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'feedback_type_text',
        'time_text'
    ];
    

    
    public function getFeedbackTypeList()
    {
        return ['功能反馈' => __('功能反馈'), '体验反馈' => __('体验反馈'), '其它' => __('其它')];
    }


    public function getFeedbackTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['feedback_type']) ? $data['feedback_type'] : '');
        $list = $this->getFeedbackTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['time']) ? $data['time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('User', 'id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
