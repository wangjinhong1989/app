<?php

namespace app\admin\model;

use think\Model;


class SearchHistory extends Model
{

    

    

    // 表名
    protected $name = 'search_history';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'time_text'
    ];
    

    
    public function getTypeList()
    {
        return ['作者' => __('作者'), '标题' => __('标题'), '描述' => __('描述'), '内容' => __('内容'), '标签' => __('标签'), '全部' => __('全部'), '其它' => __('其它')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getTypeList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['time']) ? $data['time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setTypeAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function save($data){
            // 查询是否存在， 不存在添加，存在更新时间.
            $info=self::where($data)->limit(0,1)->find();
            if(empty($info)){
                $data["time"]=time();
                self::create($data);
            }else {
                $info->time=time();
                $info->save();
            }


    }

}
