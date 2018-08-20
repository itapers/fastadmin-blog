<?php
/*
 * @Author: hippo
 * @Date: 2018-01-19 11:51:47
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-30 16:11:08
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use Cache;
use Config;
use Env;
use wind\Random;

class Ajax extends Backend
{

    /**
     * 清空系统缓存.
     */
    public function wipecache()
    {
        $wipe_cache_type = [Env::get('runtime_path') . 'temp/', Env::get('runtime_path') . 'log/', Env::get('runtime_path') . 'cache/'];
        foreach ($wipe_cache_type as $item) {
            // $dir = constant($item);
            if (!is_dir($item)) {
                continue;
            }
            rmdirs($dir);
        }
        Cache::clear();
        $this->success();
    }

    /**
     * 上传接口
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error('未上传文件或超出服务器上传限制');
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', $upload['mimetype']);
        $typeArr = explode('/', $fileInfo['type']);
        //验证文件后缀
        if ($upload['mimetype'] !== '*' && !in_array($suffix, $mimetypeArr) && !in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $root_path = Env::get('root_path');
        $splInfo = $file->validate(['size' => $size])->move($root_path . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            // $params = array(
            //     'filesize'    => $fileInfo['size'],
            //     'imagewidth'  => $imagewidth,
            //     'imageheight' => $imageheight,
            //     'imagetype'   => $suffix,
            //     'imageframes' => 0,
            //     'mimetype'    => $fileInfo['type'],
            //     'url'         => $uploadDir . $splInfo->getSaveName(),
            //     'uploadtime'  => time(),
            //     'storage'     => 'local',
            //     'sha1'        => $sha1,
            // );
            // $attachment = model("attachment");
            // $attachment->data(array_filter($params));
            // $attachment->save();
            $this->success('上传成功', null, [
                'url' => $uploadDir . $splInfo->getSaveName(),
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }
}
