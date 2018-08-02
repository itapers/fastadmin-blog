<?php

/*
 * @Author: hippo
 * @Date: 2018-01-20 19:57:24
 * @Last Modified by: hippo
 * @Last Modified time: 2018-02-07 14:28:40
 */

namespace app\admin\behavior;

class Actionlog
{
    public function run($params)
    {
        if (request()->isPost()) {
            \app\admin\model\Actionlog::record();
        }
    }
}
