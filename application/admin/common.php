<?php

/*
 * @Author: hippo
 * @Date: 2018-01-20 14:52:02
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-06-01 14:08:27
 */

use think\Db;
use think\Request;
use wind\Form;

/**
 * 生成下拉列表.
 *
 * @param string $name 下拉框name
 * @param mixed  $options 下拉框选项值
 * @param mixed  $selected 选中值
 * @param mixed  $attr 属性
 *
 * @return string
 */
function build_select($name, $options, $selected = [], $attr = [])
{
    $options = is_array($options) ? $options : explode(',', $options);
    $selected = is_array($selected) ? $selected : explode(',', $selected);

    return Form::select($name, $options, $selected, $attr);
}

/**
 * 生成单选按钮组.
 *
 * @param string $name 单选框name
 * @param array  $list 单选框选项
 * @param mixed  $selected 选中值
 *
 * @return string
 */
function build_radios($name, $list = [], $selected = null)
{
    $html = [];
    $selected = is_null($selected) ? key($list) : $selected;
    $selected = is_array($selected) ? $selected : explode(',', $selected);
    foreach ($list as $k => $v) {
        $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
    }

    return '<div class="radio">' . implode(' ', $html) . '</div>';
}

/**
 * 生成复选按钮组.
 *
 * @param string $name 复选框name
 * @param array  $list 复选框选项
 * @param mixed  $selected 选中值
 *
 * @return string
 */
function build_checkboxs($name, $list = [], $selected = null)
{
    $html = [];
    $selected = is_null($selected) ? [] : $selected;
    $selected = is_array($selected) ? $selected : explode(',', $selected);
    foreach ($list as $k => $v) {
        $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
    }

    return '<div class="checkbox">' . implode(' ', $html) . '</div>';
}

/**
 * 生成分类下拉列表框.
 *
 * @param string $name
 * @param string $type
 * @param mixed  $selected
 * @param array  $attr
 *
 * @return string
 */
function build_category_select($name, $type, $selected = null, $attr = [], $header = [])
{
    $tree = Tree::instance();
    $tree->init(Category::getCategoryArray($type), 'pid');
    $categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
    $categorydata = $header ? $header : [];
    foreach ($categorylist as $k => $v) {
        $categorydata[$v['id']] = $v['name'];
    }
    $attr = array_merge(['id' => "c-{$name}", 'class' => 'form-control selectpicker'], $attr);

    return build_select($name, $categorydata, $selected, $attr);
}

/**
 * 生成表格操作按钮栏.
 *
 * @param array $btns 按钮组
 * @param array $attr 按钮属性值
 *
 * @return string
 */
function build_toolbar($btns = null, $attr = [])
{
    $auth = \app\admin\library\Auth::instance();
    $controller = str_replace('.', '/', strtolower(Request()->controller()));
    $btns = $btns ? $btns : ['refresh', 'add', 'edit', 'del', 'import'];
    $btns = is_array($btns) ? $btns : explode(',', $btns);
    $index = array_search('delete', $btns);
    if (false !== $index) {
        $btns[$index] = 'del';
    }
    $btnAttr = [
        'refresh' => ['javascript:;', 'btn btn-primary btn-refresh', 'fa fa-refresh', '', '刷新'],
        'add' => ['javascript:;', 'btn btn-success btn-add', 'fa fa-plus', '添加', '添加'],
        'edit' => ['javascript:;', 'btn btn-success btn-edit btn-disabled disabled', 'fa fa-pencil', '编辑', '编辑'],
        'del' => ['javascript:;', 'btn btn-danger btn-del btn-disabled disabled', 'fa fa-trash', '删除', '删除'],
        'import' => ['javascript:;', 'btn btn-danger btn-import', 'fa fa-upload', '导入', '导入'],
    ];
    $btnAttr = array_merge($btnAttr, $attr);
    $html = [];
    foreach ($btns as $k => $v) {
        //如果未定义或没有权限
        if (!isset($btnAttr[$v]) || ('refresh' !== $v && !$auth->check("{$controller}/{$v}"))) {
            continue;
        }
        list($href, $class, $icon, $text, $title) = $btnAttr[$v];
        $extend = 'import' == $v ? 'id="btn-import-' . \wind\Random::alpha() . '" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"' : '';
        $html[] = '<a href="' . $href . '" class="' . $class . '" title="' . $title . '" ' . $extend . '><i class="' . $icon . '"></i> ' . $text . '</a>';
    }

    return implode(' ', $html);
}

/**
 * 生成页面Heading.
 *
 * @param string $path 指定的path
 *
 * @return string
 */
function build_heading($path = null, $container = true)
{
    $title = $content = '';
    if (is_null($path)) {
        $action = request()->action();
        $controller = str_replace('.', '/', request()->controller());
        $path = strtolower($controller . ($action && 'index' != $action ? '/' . $action : ''));
    }
    // 根据当前的URI自动匹配父节点的标题和备注
    $data = Db::name('auth_rule')->where('name', $path)->field('title,remark')->find();
    if ($data) {
        $title = $data['title'];
        $content = $data['remark'];
    }
    if (!$content) {
        return '';
    }
    $result = '<div class="panel-lead"><em>' . $title . '</em>' . $content . '</div>';
    if ($container) {
        $result = '<div class="panel-heading">' . $result . '</div>';
    }

    return $result;
}
