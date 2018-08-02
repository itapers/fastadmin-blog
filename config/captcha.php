<?php

/**
 * @Author: hippo
 * @Date:   2018-04-10 14:46:14
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-04-10 14:48:51
 */

return [
    // +----------------------------------------------------------------------
    // | 验证码设置
    // +----------------------------------------------------------------------
    // 验证码字符集合
    'codeSet' => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
    // 验证码字体大小(px)
    'fontSize' => 18,
    // 是否画混淆曲线
    'useCurve' => false,
    //使用中文验证码
    'useZh' => false,
    // 验证码图片高度
    'imageH' => 40,
    // 验证码图片宽度
    'imageW' => 130,
    // 验证码位数
    'length' => 4,
    // 验证成功后是否重置
    'reset' => true,
];
