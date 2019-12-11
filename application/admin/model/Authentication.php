<?php

namespace app\admin\model;

use think\Model;


class Authentication extends Model
{

    

    

    // 表名
    protected $name = 'authentication';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'authentication_type_text',
        'status_text',
        'time_text'
    ];
    

    
    public function getAuthenticationTypeList()
    {
        return ['个人认证' => __('个人认证'), '企业认证' => __('企业认证'), '媒体认证' => __('媒体认证')];
    }

    public function getStatusList()
    {
        return ['有效' => __('有效'), '无效' => __('无效'), '审核' => __('审核')];
    }


    public function getAuthenticationTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['authentication_type']) ? $data['authentication_type'] : '');
        $list = $this->getAuthenticationTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
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


    public function certificates()
    {
        return $this->belongsTo('Certificates', 'certificates_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
