<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Db;

// 应用公共文件
if (!function_exists('rmdirs')) {
    /**
     * 删除文件夹.
     *
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     *
     * @return bool
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }

        return true;
    }
}

if (!function_exists('array_bykeys')) {
    /**
     * 根据某些字段名获取新数组.
     *
     * @param array  $array   原数组
     * @param string $bykey   字段名
     * @param array  $toarray 新数组
     *
     * @return array 新数组
     */
    function array_bykeys($array, $bykey, $toarray = null)
    {
        if (empty($toarray)) {
            $toarray = array();
        }
        $keys = explode(',', $bykey);
        $keys = array_map('trim', $keys);
        foreach ($array as $key => $val) {
            if (in_array($key, $keys)) {
                $toarray[$key] = $val;
            }
        }

        return $toarray;
    }
}

if (!function_exists('array_coltoarray')) {
    /**
     * 二位数据根据某一键值生成新的一维数组.
     *
     * @param array  $array 原来的二位数组
     * @param string $key   键值
     *
     * @return array 新的一维数组
     */
    function array_coltoarray($array, $key)
    {
        foreach ($array as $v) {
            $newarray[] = $v[$key];
        }

        return $newarray;
    }
}

if (!function_exists('array_sort')) {
    /**
     * 二维数组排序.
     *
     * @param string $arr  二维数组
     * @param string $keys 排序键值
     * @param string $type 排序方式 asc正序 desc倒
     */
    function array_sort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ('asc' == $type) {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }

        return $new_array;
    }
}

if (!function_exists('mb_ucfirst')) {

    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }

}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}
if (!function_exists('refreshFile')) {
    /**
     * 刷新配置文件
     */
    function refreshFile()
    {
        $config = [];
        $configs = Db::table('fa_config')->select();
        foreach ($configs as $k => $v) {

            $value = $v;
            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files'])) {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array') {
                $value['value'] = (array) json_decode($value['value'], true);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(Env::get('config_path') . DIRECTORY_SEPARATOR . 'site.php', '<?php' . "\n\nreturn " . var_export($config, true) . ";");
    }
}
