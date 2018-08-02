<?php
/*
 * @Author: hippo
 * @Date: 2018-01-20 19:35:04
 * @Last Modified by: hippo
 * @Last Modified time: 2018-02-23 17:10:37
 */

namespace app\admin\model;

use think\Model;
use Session;

class Actionlog extends Model
{
    protected $name = 'action_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';

    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setContent($content)
    {
        self::$content = $content;
    }

    public static function record($tile = '')
    {
        $admin = Session::get('admin');
        $admin_id = $admin ? $admin->id : 0;
        $username = $admin ? $admin->username : '未知';
        $content = self::$content;
        if (!$content) {
            $content = request()->param();
            foreach ($content as $k => $v) {
                if (is_string($v) && strlen($v) > 200 || false !== stripos($k, 'password')) {
                    unset($content[$k]);
                }
            }
        }
        $title = self::$title;
        if (!$title) {
            $title = [];
            $breadcrumb = \app\admin\library\Auth::instance()->getBreadCrumb();
            foreach ($breadcrumb as $k => $v) {
                $title[] = $v['title'];
            }
            $title = implode(' ', $title);
        }
        self::create([
            'title' => $title,
            'content' => !is_scalar($content) ? json_encode($content) : $content,
            'url' => request()->url(),
            'uid' => $admin_id,
            'username' => $username,
            'useragent' => request()->server('HTTP_USER_AGENT'),
            'ip' => request()->ip(),
        ]);
    }
}
