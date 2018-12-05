<?php
/**
 * Created by PhpStorm.
 * User: modou
 * Date: 10/11/18
 * Time: 下午1:52
 */

namespace app\index\controller;

use QL\QueryList;
use think\Controller;
use Cache;

class CollectVideo extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['getNewsData'];

    /**
     * 调用采集方法
     * @param $url 采集的url
     * @param $rules 规则
     * @param string $range 切片选择器
     * @return mixed
     */
    protected function getQueryList($url, $rules, $range = '')
    {
        $rt = QueryList::get($url)
            ->rules($rules)
            ->range($range)
            ->query()
            ->getData();
        //数据数组形式
        $newsData = $rt->all();
        return $newsData;
    }

    /**
     * 采集视频
     * @param string $param
     * @param string $url
     * @return mixed
     */
    public function getVideoData($param = '', $url = 'http://v.qq.com/x/list/movie')
    {
        $url = $param ? $url . '?' . $param : $url;
        //元数据采集规则
        $rules = [
            'head' => ['head', 'html'],
            'body' => ['body', 'html', '-.link_logo -.mod_quick.cf -.filter_list -.site_channel -.mod_search -.footer_inner -.site_common_head']
        ];
        $key = md5(serialize($rules) . $url) . 'getVideoData';
        $result = Cache::get($key);
        if (!$result) {
            //采集的数据
            $result = $this->getQueryList($url, $rules);

            //处理url
            foreach ($result as $k => $v) {

                //批量替换url
                $ql = QueryList::html($v['body']);
                $newUrl = '/index/vedio/index?url=';
                $content = $ql->find('.figures_list')->html();
                $html = preg_replace('/href="(.*?)"/', 'href="' . $newUrl . '${1}"', $content);
                $ql->find('.figures_list')->html($html);
                $result[$k]['body'] = $ql->find('')->html();

            }

            Cache::set($key, $result);
        }

        return $result;
    }


}