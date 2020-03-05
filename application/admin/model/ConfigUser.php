<?php

namespace app\admin\model;

use think\Model;


class ConfigUser extends Model
{

    

    

    // 表名
    protected $name = 'config_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'kaiguan_text',
        'create_time_text'
    ];
    

    
    public function getKaiguanList()
    {
        return ['开启' => __('开启'), '关闭' => __('关闭')];
    }


    public function getKaiguanTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['kaiguan']) ? $data['kaiguan'] : '');
        $list = $this->getKaiguanList();
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


}
