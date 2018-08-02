<?php
/*
 * @Author: hippo
 * @Date: 2018-01-18 10:13:57
 * @Last Modified by: hippo
 * @Last Modified time: 2018-01-18 10:17:50
 */

namespace app\admin\model;

use think\Model;

class AuthRule extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}
