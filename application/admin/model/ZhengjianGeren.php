<?php

namespace app\admin\model;

use think\Model;


class ZhengjianGeren extends Model
{

    

    

    // 表名
    protected $name = 'zhengjian_geren';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'certificates_type_text',
        'create_time_text'
    ];
    

    
    public function getStatusList()
    {
        return ['审核中' => __('审核中'), '通过' => __('通过'), '不通过' => __('不通过')];
    }

    public function getCertificatesTypeList()
    {
        return ['身份证' => __('身份证')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCertificatesTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['certificates_type']) ? $data['certificates_type'] : '');
        $list = $this->getCertificatesTypeList();
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


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
