<?php
/*
 * @Author: hippo
 * @Date: 2018-01-18 15:00:46
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-07-30 17:20:01
 */

namespace app\admin\controller;

use app\common\controller\Backend;

class Dashboard extends Backend
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $result = [
                'code' => 1,
                'data' => [
                    'column' => ['男', '女', '未知'],
                    'xcolumnData' => ["近1月", "近2月", "近3月", "近4月", "近5月", "近6月"],
                    'columnData' => [
                        [
                            'name' => '男',
                            'type' => 'line',
                            'data' => ["9", "100", "195", "130", "185", "172"],
                        ],
                        [
                            'name' => '女',
                            'type' => 'line',
                            'data' => ["154", "144", "114", "174", "126", "192"],
                        ],
                        [
                            'name' => '未知',
                            'type' => 'line',
                            'data' => ["54", "44", "14", "74", "26", "92"],
                        ],
                    ],
                ],
            ];
            return json($result);

        }
        $seventtime = \wind\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++) {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $this->assign([
            'paylist' => $paylist,
            'createlist' => $createlist,
        ]);
        return $this->fetch();
    }
}
