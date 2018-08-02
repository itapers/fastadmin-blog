<?php
/*
 * @Author: hippo
 * @Date: 2018-01-18 10:18:42
 * @Last Modified by: hippo
 * @Last Modified time: 2018-01-18 10:19:54
 */

namespace app\admin\model;

use think\Model;

class AuthGroup extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}
