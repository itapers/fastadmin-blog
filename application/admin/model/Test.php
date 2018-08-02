<?php

namespace app\admin\model;

use think\Model;

class Test extends Model
{
    // 表名
    protected $name = 'test';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'week_text',
        'sexdata_text',
        'attrdata_text',
        'publishtime_text'
    ];



    public function getWeekList()
    {
        return config('site.week');
    }

    public function getSexdataList()
    {
        return config('site.sexdata');
    }

    public function getAttrdataList()
    {
        return config('site.attrdata');
    }


    public function getWeekTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['week'];
        $list = $this->getWeekList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSexdataTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['sexdata'];
        $list = $this->getSexdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAttrdataTextAttr($value, $data)
    {
        $value = $value ? $value : $data['attrdata'];
        $valueArr = explode(',', $value);
        $list = $this->getAttrdataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getPublishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['publishtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAttrdataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

}