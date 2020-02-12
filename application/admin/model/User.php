<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use think\Model;

class User extends Model
{

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text'
    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    protected static function init()
    {
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();
            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
        });


        self::beforeUpdate(function ($row) {
            $changedata = $row->getChangedData();
            if (isset($changedata['money'])) {
                $origin = $row->getOriginData();
                MoneyLog::create(['user_id' => $row['id'], 'money' => $changedata['money'] - $origin['money'], 'before' => $origin['money'], 'after' => $changedata['money'], 'memo' => '管理员变更金额']);
            }
        });
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getIsKuaixunList()
    {
        return ['normal' => __('否'), 'hidden' => __('Hidden')];
    }
    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function auth_status($user_id){
        $ae=( new \app\admin\model\AuthenticationEnterprise())->where(["user_id"=>$user_id,"status"=>"审核通过"])
            ->find();

        if($ae){
            return 1;
        }
        $am=( new \app\admin\model\AuthenticationMedia())->where(["user_id"=>$user_id])
            ->find();

        if($am){
            return 2;
        }
        $ap=( new \app\admin\model\AuthenticationPersonal())->where(["user_id"=>$user_id])
            ->find();
        if($ap){
            return 3;
        }

        return 0;
    }


    public function auth_status1($user_id){
        $ae=( new \app\admin\model\AuthenticationEnterprise())->where(["user_id"=>$user_id,"status"=>"审核通过"])
            ->find();

        if($ae){
            return "企业认证";
        }
        $am=( new \app\admin\model\AuthenticationMedia())->where(["user_id"=>$user_id])
            ->find();

        if($am){
            return "媒体认证";
        }
        $ap=( new \app\admin\model\AuthenticationPersonal())->where(["user_id"=>$user_id])
            ->find();
        if($ap){
            return "个人认证";
        }

        return "";
    }


}
