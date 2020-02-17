<?php

namespace app\admin\model;

use think\Model;


class Version extends Model
{

    

    

    // 表名
    protected $name = 'version';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'enforce_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getEnforceList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getStatusList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }


    public function getEnforceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['enforce']) ? $data['enforce'] : '');
        $list = $this->getEnforceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
