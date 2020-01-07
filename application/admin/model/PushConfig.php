<?php

namespace app\admin\model;

use think\Model;


class PushConfig extends Model
{

    

    

    // 表名
    protected $name = 'push_config';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'is_accept_notify_text',
        'is_article_notify_text',
        'is_kuaixun_notify_text',
        'need_voice_text',
        'is_follow_notify_text',
        'status_text',
        'create_time_text'
    ];
    

    
    public function getIsAcceptNotifyList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getIsArticleNotifyList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getIsKuaixunNotifyList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getNeedVoiceList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getIsFollowNotifyList()
    {
        return ['是' => __('是'), '否' => __('否')];
    }

    public function getStatusList()
    {
        return ['显示' => __('显示'), '隐藏' => __('隐藏')];
    }


    public function getIsAcceptNotifyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_accept_notify']) ? $data['is_accept_notify'] : '');
        $list = $this->getIsAcceptNotifyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsArticleNotifyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_article_notify']) ? $data['is_article_notify'] : '');
        $list = $this->getIsArticleNotifyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsKuaixunNotifyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_kuaixun_notify']) ? $data['is_kuaixun_notify'] : '');
        $list = $this->getIsKuaixunNotifyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNeedVoiceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['need_voice']) ? $data['need_voice'] : '');
        $list = $this->getNeedVoiceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsFollowNotifyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_follow_notify']) ? $data['is_follow_notify'] : '');
        $list = $this->getIsFollowNotifyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
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
