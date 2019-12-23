<?php

namespace app\admin\model;

use think\Model;


class Article extends Model
{

    

    

    // 表名
    protected $name = 'article';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'status_text',
        'is_reply_text',
        'is_mine_text'
    ];
    

    
    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }

    public function getIsReplyList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getIsMineList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsReplyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_reply']) ? $data['is_reply'] : '');
        $list = $this->getIsReplyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsMineTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_mine']) ? $data['is_mine'] : '');
        $list = $this->getIsMineList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function articletype()
    {
        return $this->belongsTo('Articletype', 'articletype_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
