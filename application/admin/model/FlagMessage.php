<?php

namespace app\admin\model;

use think\Model;


class FlagMessage extends Model
{

    

    

    // 表名
    protected $name = 'flag_message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function user()
    {
        return $this->belongsTo('User', 'id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public static function update($user_id,$type,$flag=1){
        $info=self::get(["user_id"=>$user_id]);
        if($info){
            switch ($type){
                case 1:$info->reply_flag=$flag;break;
                case 3:$info->comment_flag=$flag;break;
                case 2:$info->follow_flag=$flag;break;
                case 5:$info->system_flag=$flag;break;
                case 4:$info->dianzan_flag=$flag;break;
            }
            $info->save();
            return true;
        }else {
            return false;
        }



    }
}
