<?php

namespace app\admin\model;

use think\Model;


class Banner extends Model
{

    

    

    // 表名
    protected $name = 'banner';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'top_text',
        'begin_time_text',
        'end_time_text',
        'url_type_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getTopList()
    {
        return ['置顶' => __('置顶'), '取消置顶' => __('取消置顶')];
    }

    public function getUrlTypeList()
    {
        return ['内链' => __('内链'), '外链' => __('外链')];
    }

    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }

    public function getBannernameList()
    {
        $data=(new Bannername())->where([])->select();
        $temp=[];
        foreach ($data as $d){
            array_push($temp,["".$d["name"]=>"".$d["name"]]);
        }
        return $temp;
    }


    public function getTopTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['top']) ? $data['top'] : '');
        $list = $this->getTopList();
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


    public function getUrlTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['url_type']) ? $data['url_type'] : '');
        $list = $this->getUrlTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setBeginTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function bannername()
    {
        return $this->belongsTo('Bannername', 'bannername_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function article()
    {
        return $this->belongsTo('Article', 'article_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
