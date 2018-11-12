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

class Collectnews extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['getNewsData'];

    //采集新闻
    public function getNewsData()
    {
        $url = 'https://it.ithome.com/ityejie/';
        // 元数据采集规则
        $rules = [
            'title' => ['h2>a', 'text'],
            'link' => ['h2>a', 'href'],
            'pic' => ['.list_thumbnail>img', 'data-original'],
            'desc' => ['.memo', 'text']
        ];
        // 切片选择器
        $range = '.ulcl>li:gt(0)';
        $rt = QueryList::get($url)
            ->rules($rules)
            ->range($range)
            ->query()
            ->getData();
        $newsData = $rt->all();
        foreach ($newsData as $k=>$v) {
            $content = QueryList::get($v['link'])->find('#paragraph')->html();
            //替换为真实图片
            $content = preg_replace ('/src=".*?"/', '', $content);
            $content = str_replace("data-original","src",$content);

            $newsData[$k]['content'] = $content;
            $newsData[$k]['createtime'] = time();
            $newsData[$k]['category_id'] = 1;
        }
        model('Article')->insertAll($newsData);
    }
}