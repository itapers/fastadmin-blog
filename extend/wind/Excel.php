<?php

/**
 * @Author: hippo
 * @Date:   2018-05-08 14:48:11
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-08 14:59:30
 */
namespace wind;

use PHPExcel;
use PHPExcel_IOFactory;

class Excel
{
    /**
     * 数据转csv格式的excle
     * @param  array $data      需要转的数组
     * @param  string $header   要生成的excel表头
     * @param  string $filename 生成的excel文件名
     *      示例数组：
    $data = array(
    '1,2,3,4,5',
    '6,7,8,9,0',
    '1,3,5,6,7'
    );
    $header='用户名,密码,头像,性别,手机号';
     */
    public static function create_csv($data, $header = null, $filename = 'simple.csv')
    {
        // 如果手动设置表头；则放在第一行
        if (!is_null($header)) {
            array_unshift($data, $header);
        }
        // 防止没有添加文件后缀
        $filename = str_replace('.csv', '', $filename) . '.csv';
        ob_clean();
        Header("Content-type:  application/octet-stream ");
        Header("Accept-Ranges:  bytes ");
        Header("Content-Disposition:  attachment;  filename=" . $filename);
        foreach ($data as $k => $v) {
            // 如果是二维数组；转成一维
            if (is_array($v)) {
                $v = implode(',', $v);
            }
            // 替换掉换行
            $v = preg_replace('/\s*/', '', $v);
            // 解决导出的数字会显示成科学计数法的问题
            $v = str_replace(',', "\t,", $v);
            // 转成gbk以兼容office乱码的问题
            echo iconv('UTF-8', 'GBK', $v) . "\t\r\n";
        }
    }

    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(
    array(NULL, 2010, 2011, 2012),
    array('Q1',   12,   15,   21),
    array('Q2',   56,   73,   86),
    array('Q3',   52,   61,   69),
    array('Q4',   30,   32,    0),
    );
     */
    public static function create_xls($data, $property = array(), $export = true)
    {
        set_time_limit(0);
        $phpexcel = new PHPExcel();
        $creator = isset($property['creator']) ? $property['creator'] : ''; //作者
        $modifier = isset($property['modifier']) ? $property['modifier'] : ''; //修改人
        $title = isset($property['title']) ? $property['title'] : ''; //标题
        $subject = isset($property['subject']) ? $property['subject'] : ''; //主题
        $description = isset($property['description']) ? $property['description'] : ''; //描述
        $keywords = isset($property['keywords']) ? $property['keywords'] : ''; //关键字
        $category = isset($property['category']) ? $property['category'] : ''; //分类
        $phpexcel->getProperties()
            ->setCreator($creator)->setLastModifiedBy($modifier)
            ->setTitle($title)->setSubject($subject)
            ->setDescription($description)
            ->setKeywords($keywords)
            ->setCategory($category);
        if ($property['needSetWidthCol']) {
            foreach ($property['needSetWidthCol'] as $v) {
                $phpexcel->getActiveSheet()->getStyle($v)->getNumberFormat()
                    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $phpexcel->getActiveSheet()->getColumnDimension($v)->setWidth(15);
            }
        }

        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        $format = $property['format'] ? $property['format'] : 'Excel5';
        $filename = $property['filename'] ? $property['filename'] . '.xls' : date('YmdHis') . '.xls';
        if ($export) {
            header("Content-type:application/vnd.ms-excel");
            header("Content-Disposition:filename=" . iconv('utf-8', 'gbk', $filename));
            header('Pragma: cache');
            header('Cache-Control: public, must-revalidate, max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, $format);
            $objWriter->save('php://output');
        } else {
            $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, $format);
            $objWriter->save($filename);
        }
    }

    /**
     * 导入excel文件
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    public static function read_xls($file)
    {
        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        $type = $type === 'csv' ? $type : 'Excel5';
        ini_set('max_execution_time', '0');
        // 判断使用哪种格式
        $objReader = PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //循环读取excel文件,读取一条,插入一条
        $data = array();
        //从第一行开始读取数据
        for ($j = 1; $j <= $highestRow; $j++) {
            //从A列读取数据
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                // 读取单元格
                $data[$j][] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            }
        }
        return $data;
    }
}
