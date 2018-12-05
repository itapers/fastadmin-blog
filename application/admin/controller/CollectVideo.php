<?php
/**
 * Created by PhpStorm.
 * User: modou
 * Date: 10/11/18
 * Time: 下午1:52
 */

namespace app\admin\controller;

use QL\QueryList;
use think\Controller;

class CollectVideo extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['getNewsData'];

    //调用采集方法
    public function getQueryList($url, $rules, $range = '')
    {
        $rt = QueryList::get($url)
            ->rules($rules)
            ->range($range)
            ->query()
            ->getData();
        $newsData = $rt->all();
        return $newsData;
    }

    //采集业界
    public function getVideoData()
    {
        $url = 'http://v.qq.com/x/list/movie';
        // 元数据采集规则
        $rules = [
            'head' => ['head', 'html'],
            'body' => ['body', 'html']
        ];

        //采集的数据
        $newsData = $this->getQueryList($url, $rules);
        return $newsData;
    }


}