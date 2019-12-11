<?php

namespace app\admin\model;

use think\Model;


class Lihaokong extends Model
{

    

    

    // 表名
    protected $name = 'lihaokong';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_profit_text',
        'time_text'
    ];
    

    
    public function getIsProfitList()
    {
        return ['利空' => __('利空'), '利好' => __('利好')];
    }


    public function getIsProfitTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_profit']) ? $data['is_profit'] : '');
        $list = $this->getIsProfitList();
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
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function article()
    {
        return $this->belongsTo('Article', 'article_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
