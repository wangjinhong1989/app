<?php

namespace app\admin\model;

use think\Model;


class Guanzhu extends Model
{

    

    

    // 表名
    protected $name = 'guanzhu';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'time_text'
    ];
    

    



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
        return $this->belongsTo('User', 'follow_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function initUser($user_id){
        $data=self::where([])->select();
        foreach ($data as $v){
            (new Guanzhu())->create([
                "user_id"=>$user_id,
                "follow_id"=>$v->user_id,
                "is_push"=>"是",
                "time"=>time()
            ]);
        }
    }
}
