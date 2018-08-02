<?php

namespace app\admin\model;

use think\Model;

class Article extends Model
{
    // 表名
    protected $name = 'article';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'attrdata_text',
        'publishtime_text'
    ];



    public function getAttrdataList()
    {
        return config('site.attrdata');
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

    public function category()
    {
        return $this->belongsTo('Category', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}