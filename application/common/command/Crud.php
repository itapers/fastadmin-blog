<?php

/**
 * @Author: hippo
 * @Date:   2018-05-20 14:19:35
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-07-19 11:30:04
 */
namespace app\common\command;

use Env;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Loader;
use wind\Form;

class Crud extends Command
{
    protected $stubList = [];
    /**
     * Selectpage搜索字段关联
     */
    protected $fieldSelectpageMap = [
        // 'nickname' => ['user_id', 'user_ids', 'admin_id', 'admin_ids'],
    ];
    /**
     * Int类型识别为日期时间的结尾字符,以下数组中字符串结尾的字段,默认会识别为日期文本框
     */
    protected $intDateSuffix = ['time'];
    /**
     * Enum类型识别为单选框的结尾字符,以下数组中字符串结尾的字段,默认会识别为单选下拉列表
     *
     */
    protected $enumRadioSuffix = ['data', 'state', 'status'];

    /**
     * Set类型识别为复选框的结尾字符,以下数组中字符串结尾的字段,默认会识别为多选下拉列表
     */
    protected $setCheckboxSuffix = ['data', 'state', 'status'];

    /**
     * 开关后缀
     */
    protected $switchSuffix = ['switch'];

    /**
     * 城市后缀
     */
    protected $citySuffix = ['city'];

    /**
     * Selectpage对应的后缀
     */
    protected $selectpageSuffix = ['_id', '_ids'];

    /**
     * Selectpage多选对应的后缀
     */
    protected $selectpagesSuffix = ['_ids'];

    /**
     * 识别为图片字段
     */
    protected $imageField = ['image', 'images', 'avatar', 'avatars', 'pic', 'img'];

    /**
     * 识别为文件字段
     */
    protected $fileField = ['file', 'files'];

    /**
     * 保留字段
     */
    protected $reservedField = ['createtime', 'updatetime'];

    /**
     * 排除字段
     */
    protected $ignoreFields = [];

    /**
     * 以指定字符结尾的字段格式化函数
     */
    protected $fieldFormatterSuffix = [
        'status' => ['type' => ['varchar'], 'name' => 'status'],
        'icon' => 'icon',
        'flag' => 'flag',
        'url' => 'url',
        'image' => 'image',
        'images' => 'images',
        'pic' => 'image',
        'img' => 'image',
        'time' => ['type' => ['int', 'timestamp'], 'name' => 'datetime'],
    ];

    /**
     * 排序字段
     */
    protected $sortField = 'id';

    /**
     * 编辑器的Class
     */
    protected $editorClass = 'editor';

    protected $defaultModule = 'admin';

    protected function configure()
    {
        $this->setName('crud')
        //增加一个选项
            ->addOption('table', 't', Option::VALUE_REQUIRED, 'table name', null) //表名，必须
            ->addOption('controller', 'c', Option::VALUE_OPTIONAL, 'controller name', null) //控制器名字
            ->addOption('model', 'm', Option::VALUE_OPTIONAL, 'model name', null) //模型名字
            ->addOption('fields', 'i', Option::VALUE_OPTIONAL, 'model visible fields', null)
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force override or force delete,without tips', null)
            ->addOption('relation', 'r', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation table name without prefix', null)
            ->addOption('relationmodel', 'e', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation model name', null)
            ->addOption('relationforeignkey', 'k', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation foreign key', null)
            ->addOption('relationprimarykey', 'p', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation primary key', null)
            ->addOption('relationfields', 's', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation table fields', null)
            ->addOption('relationmode', 'o', Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'relation table mode,hasone or belongsto', null)
            ->addOption('local', 'l', Option::VALUE_OPTIONAL, 'local model', 1)
            ->setDescription('Build CRUD controller and model from table');
    }

    protected function execute(Input $input, Output $output)
    {
        //默认生成位置
        $adminPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $this->defaultModule . DIRECTORY_SEPARATOR;
        //表名
        $table = $input->getOption('table') ?: '';
        //自定义控制器
        $controller = $input->getOption('controller');
        //自定义模型名字
        $model = $input->getOption('model');
        //强制覆盖
        $force = $input->getOption('force');
        //是否为本地model,为0时表示为全局model将会把model放在app/common/model中
        $local = $input->getOption('local');
        //自定义显示字段
        $fields = $input->getOption('fields');
        if (!$table) {
            throw new Exception('table name can\'t empty');
        }

        //关联表
        $relation = $input->getOption('relation');
        //自定义关联表模型
        $relationModel = $input->getOption('relationmodel');
        //模式
        $relationMode = $mode = $input->getOption('relationmode');
        //外键
        $relationForeignKey = $input->getOption('relationforeignkey');
        //主键
        $relationPrimaryKey = $input->getOption('relationprimarykey');
        //关联表显示字段
        $relationFields = $input->getOption('relationfields');

        $dbname = config('database.database');
        $prefix = config('database.prefix');

        //检查主表
        $modelName = $table = stripos($table, $prefix) === 0 ? substr($table, strlen($prefix)) : $table;
        $modelTableType = 'table';
        $modelTableTypeName = $modelTableName = $modelName;
        $modelTableInfo = Db::query("SHOW TABLE STATUS LIKE '{$modelTableName}'", [], true);
        if (!$modelTableInfo) {
            $modelTableType = 'name';
            $modelTableName = $prefix . $modelName;
            $modelTableInfo = Db::query("SHOW TABLE STATUS LIKE '{$modelTableName}'", [], true);
            if (!$modelTableInfo) {
                throw new Exception("table not found");
            }
        }
        $modelTableInfo = $modelTableInfo[0];
        $relations = [];
        //检查关联表
        if ($relation) {
            $relationArr = $relation;

            foreach ($relationArr as $index => $relationTable) {
                $relationName = stripos($relationTable, $prefix) === 0 ? substr($relationTable, strlen($prefix)) : $relationTable;
                $relationTableType = 'table';
                $relationTableTypeName = $relationTableName = $relationName;
                $relationTableInfo = Db::query("SHOW TABLE STATUS LIKE '{$relationTableName}'", [], true);
                if (!$relationTableInfo) {
                    $relationTableType = 'name';
                    $relationTableName = $prefix . $relationName;
                    $relationTableInfo = Db::query("SHOW TABLE STATUS LIKE '{$relationTableName}'", [], true);
                    if (!$relationTableInfo) {
                        throw new Exception("relation table not found");
                    }
                }
                $relationTableInfo = $relationTableInfo[0];
                $relationModel = isset($relationModel[$index]) ? $relationModel[$index] : '';
                $app_path = Env::get('app_path');
                //关联模型默认以表名进行处理,以下划线进行分隔,如果需要自定义则需要传入relationmodel,不支持目录层级
                $relationName = $this->getModelName($relationModel, $relationName);
                $relationFile = ($local ? $adminPath : $app_path . 'common' . DIRECTORY_SEPARATOR) . 'model' . DIRECTORY_SEPARATOR . $relationName . '.php';

                $relations[] = [
                    //关联表基础名
                    'relationName' => $relationName,
                    //关联模型名
                    'relationModel' => $relationModel,
                    //关联文件
                    'relationFile' => $relationFile,
                    //关联表名称
                    'relationTableName' => $relationTableName,
                    //关联表信息
                    'relationTableInfo' => $relationTableInfo,
                    //关联模型表类型(name或table)
                    'relationTableType' => $relationTableType,
                    //关联模型表类型名称
                    'relationTableTypeName' => $relationTableTypeName,
                    //关联模式
                    'relationFields' => isset($relationFields[$index]) ? explode(',', $relationFields[$index]) : [],
                    //关联模式
                    'relationMode' => isset($relationMode[$index]) ? $relationMode[$index] : 'belongsto',
                    //关联表外键
                    'relationForeignKey' => isset($relationForeignKey[$index]) ? $relationForeignKey[$index] : Loader::parseName($relationName) . '_id',
                    //关联表主键
                    'relationPrimaryKey' => isset($relationPrimaryKey[$index]) ? $relationPrimaryKey[$index] : '',
                ];
            }
        }

        $root_path = Env::get('root_path');

        //控制器默认以表名进行处理,以下划线进行分隔,如果需要自定义则需要传入controller,格式为目录层级
        $controller = str_replace('_', '', $controller);
        $controllerArr = !$controller ? explode('_', strtolower($table)) : explode('/', strtolower($controller));
        $controllerUrl = implode('/', $controllerArr);
        $controllerName = ucfirst(array_pop($controllerArr));
        $controllerDir = implode(DIRECTORY_SEPARATOR, $controllerArr);
        $controllerFile = ($controllerDir ? $controllerDir . DIRECTORY_SEPARATOR : '') . $controllerName . '.php';
        $viewDir = $adminPath . 'view' . DIRECTORY_SEPARATOR . $controllerUrl . DIRECTORY_SEPARATOR;
        //生成文件路径，控制器文件路径，js文件，视图层文件
        $controllerFile = $adminPath . 'controller' . DIRECTORY_SEPARATOR . $controllerFile;
        $javascriptFile = $root_path . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . $controllerUrl . '.js';
        $addFile = $viewDir . 'add.html';
        $editFile = $viewDir . 'edit.html';
        $indexFile = $viewDir . 'index.html';

        //模型默认以表名进行处理,以下划线进行分隔,如果需要自定义则需要传入model,不支持目录层级
        $modelName = $this->getModelName($model, $table);

        $modelFile = ($local ? $adminPath : $app_path . 'common' . DIRECTORY_SEPARATOR) . 'model' . DIRECTORY_SEPARATOR . $modelName . '.php';

        //非覆盖模式时如果存在控制器文件则报错
        if (is_file($controllerFile) && !$force) {
            throw new Exception("controller already exists!\nIf you need to rebuild again, use the parameter --force=true ");
        }

        //非覆盖模式时如果存在模型文件则报错
        if (is_file($modelFile) && !$force) {
            throw new Exception("model already exists!\nIf you need to rebuild again, use the parameter --force=true ");
        }
        //从数据库中获取表字段信息
        $sql = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        //加载主表的列
        $columnList = Db::query($sql, [$dbname, $modelTableName]);
        $fieldArr = [];
        foreach ($columnList as $k => $v) {
            $fieldArr[] = $v['COLUMN_NAME'];
        }

        // 加载关联表的列
        foreach ($relations as $index => &$relation) {
            $relationColumnList = Db::query($sql, [$dbname, $relation['relationTableName']]);

            $relationFieldList = [];
            foreach ($relationColumnList as $k => $v) {
                $relationFieldList[] = $v['COLUMN_NAME'];
            }
            if (!$relation['relationPrimaryKey']) {
                foreach ($relationColumnList as $k => $v) {
                    if ($v['COLUMN_KEY'] == 'PRI') {
                        $relation['relationPrimaryKey'] = $v['COLUMN_NAME'];
                        break;
                    }
                }
            }
            // 如果主键为空
            if (!$relation['relationPrimaryKey']) {
                throw new Exception('Relation Primary key not found!');
            }
            // 如果主键不在表字段中
            if (!in_array($relation['relationPrimaryKey'], $relationFieldList)) {
                throw new Exception('Relation Primary key not found in table!');
            }
            $relation['relationColumnList'] = $relationColumnList;
            $relation['relationFieldList'] = $relationFieldList;
        }
        unset($relation);

        $addList = [];
        $editList = [];
        $javascriptList = [];
        $langList = [];
        $field = 'id';
        $order = 'id';
        // $priDefined = false;
        $priKey = '';
        $relationPrimaryKey = '';
        foreach ($columnList as $k => $v) {
            if ($v['COLUMN_KEY'] == 'PRI') {
                $priKey = $v['COLUMN_NAME'];
                break;
            }
        }
        if (!$priKey) {
            throw new Exception('Primary key not found!');
        }

        $order = $priKey;

        //如果是关联模型
        foreach ($relations as $index => &$relation) {
            if ($relation['relationMode'] == 'hasone') {
                $relationForeignKey = $relation['relationForeignKey'] ? $relation['relationForeignKey'] : $table . "_id";
                $relationPrimaryKey = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $priKey;

                if (!in_array($relationForeignKey, $relation['relationFieldList'])) {
                    throw new Exception('relation table [' . $relation['relationTableName'] . '] must be contain field [' . $relationForeignKey . ']');
                }
                if (!in_array($relationPrimaryKey, $fieldArr)) {
                    throw new Exception('table [' . $modelTableName . '] must be contain field [' . $relationPrimaryKey . ']');
                }
            } else {
                $relationForeignKey = $relation['relationForeignKey'] ? $relation['relationForeignKey'] : Loader::parseName($relation['relationName']) . "_id";
                $relationPrimaryKey = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $relation['relationPriKey'];
                if (!in_array($relationForeignKey, $fieldArr)) {
                    throw new Exception('table [' . $modelTableName . '] must be contain field [' . $relationForeignKey . ']');
                }
                if (!in_array($relationPrimaryKey, $relation['relationFieldList'])) {
                    throw new Exception('relation table [' . $relation['relationTableName'] . '] must be contain field [' . $relationPrimaryKey . ']');
                }
            }
            $relation['relationForeignKey'] = $relationForeignKey;
            $relation['relationPrimaryKey'] = $relationPrimaryKey;
        }
        unset($relation);

        try {
            //设置Html为非转义模式
            Form::setEscapeHtml(false);
            $setAttrArr = [];
            $getAttrArr = [];
            $getEnumArr = [];
            $appendAttrList = [];
            $controllerAssignList = [];

            //循环所有字段,开始构造视图的HTML和JS信息
            foreach ($columnList as $k => $v) {
                $field = $v['COLUMN_NAME'];
                $fieldComment = $v['COLUMN_COMMENT'];
                $itemArr = [];
                // 这里构建Enum和Set类型的列表数据
                if (in_array($v['DATA_TYPE'], ['enum', 'set', 'tinyint'])) {
                    $itemArr = substr($v['COLUMN_TYPE'], strlen($v['DATA_TYPE']) + 1, -1);
                    $itemArr = explode(',', str_replace("'", '', $itemArr));
                    $itemArr = $this->getItemArray($itemArr, $field, $v['COLUMN_COMMENT']);
                    //如果类型为tinyint且有使用备注数据
                    if ($itemArr && $v['DATA_TYPE'] == 'tinyint') {
                        $v['DATA_TYPE'] = 'enum';
                    }
                }
                $inputType = '';
                //createtime和updatetime是保留字段不能修改和添加
                if ($v['COLUMN_KEY'] != 'PRI' && !in_array($field, $this->reservedField) && !in_array($field, $this->ignoreFields)) {
                    $inputType = $this->getFieldType($v);

                    // 如果是number类型时增加一个步长
                    $step = $inputType == 'number' && $v['NUMERIC_SCALE'] > 0 ? "0." . str_repeat(0, $v['NUMERIC_SCALE'] - 1) . "1" : 0;

                    $attrArr = ['id' => "c-{$field}"];
                    $cssClassArr = ['form-control'];
                    $fieldName = "row[{$field}]";
                    $defaultValue = $v['COLUMN_DEFAULT'];
                    $editValue = "{\$row.{$field}}";
                    // 如果默认值非null,则是一个必选项
                    if ($v['IS_NULLABLE'] == 'NO') {
                        $attrArr['data-rule'] = 'required';
                    }

                    if ($inputType == 'select') {
                        $cssClassArr[] = 'selectpicker';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        if ($v['DATA_TYPE'] == 'set') {
                            $attrArr['multiple'] = '';
                            $fieldName .= "[]";
                        }
                        $attrArr['name'] = $fieldName;

                        $this->getEnum($getEnumArr, $controllerAssignList, $field, $itemArr, $v['DATA_TYPE'] == 'set' ? 'multiple' : 'select');

                        //添加一个获取器
                        $this->getAttr($getAttrArr, $field, $v['DATA_TYPE'] == 'set' ? 'multiple' : 'select');
                        if ($v['DATA_TYPE'] == 'set') {
                            $this->setAttr($setAttrArr, $field, $inputType);
                        }
                        $this->appendAttr($appendAttrList, $field);
                        $formAddElement = $this->getReplacedTpl('html/select', ['field' => $field, 'fieldName' => $fieldName, 'fieldList' => $this->getFieldListName($field), 'attrStr' => Form::attributes($attrArr), 'selectedValue' => $defaultValue]);
                        $formEditElement = $this->getReplacedTpl('html/select', ['field' => $field, 'fieldName' => $fieldName, 'fieldList' => $this->getFieldListName($field), 'attrStr' => Form::attributes($attrArr), 'selectedValue' => "\$row.{$field}"]);
                    } else if ($inputType == 'datetime') {
                        $cssClassArr[] = 'datetimepicker';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $format = "YYYY-MM-DD HH:mm:ss";
                        $phpFormat = "Y-m-d H:i:s";
                        $fieldFunc = '';
                        switch ($v['DATA_TYPE']) {
                            case 'year';
                                $format = "YYYY";
                                $phpFormat = 'Y';
                                break;
                            case 'date';
                                $format = "YYYY-MM-DD";
                                $phpFormat = 'Y-m-d';
                                break;
                            case 'time';
                                $format = "HH:mm:ss";
                                $phpFormat = 'H:i:s';
                                break;
                            case 'timestamp';
                                $fieldFunc = 'datetime';
                            case 'datetime';
                                $format = "YYYY-MM-DD HH:mm:ss";
                                $phpFormat = 'Y-m-d H:i:s';
                                break;
                            default:
                                $fieldFunc = 'datetime';
                                $this->getAttr($getAttrArr, $field, $inputType);
                                $this->setAttr($setAttrArr, $field, $inputType);
                                $this->appendAttr($appendAttrList, $field);
                                break;
                        }
                        $defaultDateTime = "{:date('{$phpFormat}')}";
                        $attrArr['data-date-format'] = $format;
                        $attrArr['data-use-current'] = "true";
                        $fieldFunc = $fieldFunc ? "|{$fieldFunc}" : "";
                        $formAddElement = Form::text($fieldName, $defaultDateTime, $attrArr);
                        $formEditElement = Form::text($fieldName, "{\$row.{$field}{$fieldFunc}}", $attrArr);
                    } else if ($inputType == 'checkbox' || $inputType == 'radio') {
                        unset($attrArr['data-rule']);
                        $fieldName = $inputType == 'checkbox' ? $fieldName .= "[]" : $fieldName;
                        $attrArr['name'] = "row[{$fieldName}]";

                        $this->getEnum($getEnumArr, $controllerAssignList, $field, $itemArr, $inputType);
                        //添加一个获取器
                        $this->getAttr($getAttrArr, $field, $inputType);
                        if ($inputType == 'checkbox') {
                            $this->setAttr($setAttrArr, $field, $inputType);
                        }
                        $this->appendAttr($appendAttrList, $field);
                        $defaultValue = $inputType == 'radio' && !$defaultValue ? key($itemArr) : $defaultValue;

                        $formAddElement = $this->getReplacedTpl('html/' . $inputType, ['field' => $field, 'fieldName' => $fieldName, 'fieldList' => $this->getFieldListName($field), 'attrStr' => Form::attributes($attrArr), 'selectedValue' => $defaultValue]);
                        $formEditElement = $this->getReplacedTpl('html/' . $inputType, ['field' => $field, 'fieldName' => $fieldName, 'fieldList' => $this->getFieldListName($field), 'attrStr' => Form::attributes($attrArr), 'selectedValue' => "\$row.{$field}"]);
                    } else if ($inputType == 'textarea') {
                        $cssClassArr[] = substr($field, -7) == 'content' ? $this->editorClass : '';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['rows'] = 5;
                        $formAddElement = Form::textarea($fieldName, $defaultValue, $attrArr);
                        $formEditElement = Form::textarea($fieldName, $editValue, $attrArr);
                    } else if ($inputType == 'switch') {
                        unset($attrArr['data-rule']);
                        if ($defaultValue === '1' || $defaultValue === 'Y') {
                            $yes = $defaultValue;
                            $no = $defaultValue === '1' ? '0' : 'N';
                        } else {
                            $no = $defaultValue;
                            $yes = $defaultValue === '0' ? '1' : 'Y';
                        }
                        $formAddElement = $formEditElement = Form::hidden($fieldName, $no, array_merge(['checked' => ''], $attrArr));
                        $attrArr['id'] = $fieldName . "-switch";
                        $formAddElement .= sprintf(Form::label("{$attrArr['id']}", "%s 是", ['class' => 'control-label']), Form::checkbox($fieldName, $yes, $defaultValue === $yes, $attrArr));
                        $formEditElement .= sprintf(Form::label("{$attrArr['id']}", "%s 是", ['class' => 'control-label']), Form::checkbox($fieldName, $yes, 0, $attrArr));
                        $formEditElement = str_replace('type="checkbox"', 'type="checkbox" {in name="' . "\$row.{$field}" . '" value="' . $yes . '"}checked{/in}', $formEditElement);
                    } else if ($inputType == 'citypicker') {
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['data-toggle'] = "city-picker";
                        $formAddElement = sprintf("<div class='control-relative'>%s</div>", Form::input('text', $fieldName, $defaultValue, $attrArr));
                        $formEditElement = sprintf("<div class='control-relative'>%s</div>", Form::input('text', $fieldName, $editValue, $attrArr));
                    } else {
                        $search = $replace = '';
                        //特殊字段为关联搜索
                        if ($this->isMatchSuffix($field, $this->selectpageSuffix)) {
                            $inputType = 'text';
                            $defaultValue = '';
                            $attrArr['data-rule'] = 'required';
                            $cssClassArr[] = 'selectpage';
                            $selectpageController = str_replace('_', '/', substr($field, 0, strripos($field, '_')));
                            $attrArr['data-source'] = $selectpageController . "/index";
                            //如果是类型表需要特殊处理下
                            if ($selectpageController == 'category') {
                                $attrArr['data-source'] = 'category/selectpage';
                                $attrArr['data-params'] = '##replacetext##';
                                $search = '"##replacetext##"';
                                // $replace = '\'{"custom[type]":"' . $table . '"}\'';
                            }
                            if ($this->isMatchSuffix($field, $this->selectpagesSuffix)) {
                                $attrArr['data-multiple'] = 'true';
                            }
                            foreach ($this->fieldSelectpageMap as $m => $n) {
                                if (in_array($field, $n)) {
                                    $attrArr['data-field'] = $m;
                                    break;
                                }
                            }
                        }
                        //因为有自动完成可输入其它内容
                        $step = array_intersect($cssClassArr, ['selectpage']) ? 0 : $step;
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $isUpload = false;
                        if ($this->isMatchSuffix($field, array_merge($this->imageField, $this->fileField))) {
                            $isUpload = true;
                        }
                        //如果是步长则加上步长
                        if ($step) {
                            $attrArr['step'] = $step;
                        }
                        //如果是图片加上个size
                        if ($isUpload) {
                            $attrArr['size'] = 50;
                        }

                        $formAddElement = Form::input($inputType, $fieldName, $defaultValue, $attrArr);
                        $formEditElement = Form::input($inputType, $fieldName, $editValue, $attrArr);
                        if ($search && $replace) {
                            $formAddElement = str_replace($search, $replace, $formAddElement);
                            $formEditElement = str_replace($search, $replace, $formEditElement);
                        }
                        //如果是图片或文件
                        if ($isUpload) {
                            $formAddElement = $this->getImageUpload($field, $formAddElement);
                            $formEditElement = $this->getImageUpload($field, $formEditElement);
                        }
                    }
                    //构造添加和编辑HTML信息
                    $addList[] = $this->getFormGroup($field, $fieldComment, $formAddElement);
                    $editList[] = $this->getFormGroup($field, $fieldComment, $formEditElement);
                }

                //过滤text类型字段
                if ($v['DATA_TYPE'] != 'text') {
                    if (!$fields || in_array($field, explode(',', $fields))) {
                        //构造JS列信息
                        $javascriptList[] = $this->getJsColumn($field, $fieldComment, $v['DATA_TYPE'], $k + 1);
                    }
                    //排序方式,如果有指定排序字段,否则按主键排序
                    $order = $field == $this->sortField ? $this->sortField : $order;
                }
            }

            //循环关联表,追加语言包和JS列
            foreach ($relations as $index => $relation) {
                foreach ($relation['relationColumnList'] as $k => $v) {
                    // 不显示的字段直接过滤掉
                    if ($relation['relationFields'] && !in_array($v['COLUMN_NAME'], $relation['relationFields'])) {
                        continue;
                    }

                    $relationField = strtolower($relation['relationName']) . "." . $v['COLUMN_NAME'];

                    //过滤text类型字段
                    if ($v['DATA_TYPE'] != 'text') {
                        //构造JS列信息
                        $javascriptList[] = $this->getJsColumn($relationField, $v['COLUMN_COMMENT'], $v['DATA_TYPE'], $k + 1);
                    }
                }
            }

            //JS最后一列加上操作列
            $javascriptList[] = str_repeat(" ", 20) . '{ "needControlShow":false, "title": "操作", "data":"id", "orderable": false,' . str_repeat(" ", 1) . '
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.operate("id",data)
                        }
                    }';
            $addList = implode("\n", array_filter($addList));
            $editList = implode("\n", array_filter($editList));
            $javascriptList = implode(",\n", array_filter($javascriptList));
            //表注释
            $tableComment = $modelTableInfo['Comment'];
            $tableComment = mb_substr($tableComment, -1) == '表' ? mb_substr($tableComment, 0, -1) . '管理' : $tableComment;
            //获取应用名称
            $appNamespace = config('app_name');
            //默认模块
            $moduleName = $this->defaultModule;
            //控制器的命名空间
            $controllerNamespace = "{$appNamespace}\\{$moduleName}\\controller" . ($controllerDir ? "\\" : "") . str_replace('/', "\\", $controllerDir);
            //模型命名空间
            $modelNamespace = "{$appNamespace}\\" . ($local ? $moduleName : "common") . "\\model";
            $modelInit = '';
            if ($priKey != $order) {
                $modelInit = $this->getReplacedTpl('mixins' . DIRECTORY_SEPARATOR . 'modelinit', ['order' => $order]);
            }
            $data = [
                'controllerNamespace' => $controllerNamespace,
                'modelNamespace' => $modelNamespace,
                'controllerUrl' => $controllerUrl,
                'controllerDir' => $controllerDir,
                'controllerName' => $controllerName,
                'controllerAssignList' => implode("\n", $controllerAssignList),
                'modelName' => $modelName,
                'modelTableName' => $modelTableName,
                'modelTableType' => $modelTableType,
                'modelTableTypeName' => $modelTableTypeName,
                'tableComment' => $tableComment,
                'pk' => $priKey,
                'order' => $order,
                'table' => $table,
                'tableName' => $modelTableName,
                'addList' => $addList,
                'editList' => $editList,
                'javascriptList' => $javascriptList,
                'modelAutoWriteTimestamp' => in_array('createtime', $fieldArr) || in_array('updatetime', $fieldArr) ? "'int'" : 'false',
                'createTime' => in_array('createtime', $fieldArr) ? "'createtime'" : 'false',
                'updateTime' => in_array('updatetime', $fieldArr) ? "'updatetime'" : 'false',
                'relationSearch' => $relations ? 'true' : 'false',
                'relationWithList' => '',
                'relationMethodList' => '',
                'visibleFieldList' => $fields ? "\$row->visible(['" . implode("','", array_filter(explode(',', $fields))) . "']);" : '',
                'appendAttrList' => implode(",\n", $appendAttrList),
                'getEnumList' => implode("\n\n", $getEnumArr),
                'getAttrList' => implode("\n\n", $getAttrArr),
                'setAttrList' => implode("\n\n", $setAttrArr),
                'modelInit' => $modelInit,
            ];
            //如果使用关联模型
            if ($relations) {
                $relationWithList = $relationMethodList = $relationVisibleFieldList = [];
                foreach ($relations as $index => $relation) {
                    //需要构造关联的方法
                    $relation['relationMethod'] = strtolower($relation['relationName']);

                    //关联的模式
                    $relation['relationMode'] = $relation['relationMode'] == 'hasone' ? 'hasOne' : 'belongsTo';

                    //关联字段
                    $relation['relationForeignKey'] = $relation['relationForeignKey'];
                    $relation['relationPrimaryKey'] = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $priKey;

                    //预载入的方法
                    $relationWithList[] = $relation['relationMethod'];

                    unset($relation['relationColumnList'], $relation['relationFieldList'], $relation['relationTableInfo']);

                    //构造关联模型的方法
                    $relationMethodList[] = $this->getReplacedTpl('mixins' . DIRECTORY_SEPARATOR . 'modelrelationmethod', $relation);

                    //如果设置了显示主表字段，则必须显式将关联表字段显示
                    if ($fields) {
                        $relationVisibleFieldList[] = "\$row->visible(['{$relation['relationMethod']}']);";
                    }

                    //显示的字段
                    if ($relation['relationFields']) {
                        $relationVisibleFieldList[] = "\$row->getRelation('" . $relation['relationMethod'] . "')->visible(['" . implode("','", $relation['relationFields']) . "']);";
                    }
                }

                $data['relationWithList'] = "->with(['" . implode("','", $relationWithList) . "'])";
                $data['relationMethodList'] = implode("\n\n", $relationMethodList);
                $data['relationVisibleFieldList'] = implode("\n\t\t\t\t", $relationVisibleFieldList);

                //需要重写index方法
                $data['controllerIndex'] = $this->getReplacedTpl('controllerindex', $data);

            } else if ($fields) {
                $data = array_merge($data, ['relationWithList' => '', 'relationMethodList' => '', 'relationVisibleFieldList' => '']);
                //需要重写index方法
                $data['controllerIndex'] = $this->getReplacedStub('controllerindex', $data);
            } else {
                $data = array_merge($data, ['relationWithList' => '', 'relationMethodList' => '', 'relationVisibleFieldList' => '']);
                //需要重写index方法
                $data['controllerIndex'] = '';
            }
            // 生成控制器文件
            $result = $this->writeToFile('controller', $data, $controllerFile);
            // 生成模型文件
            $result = $this->writeToFile('model', $data, $modelFile);
            if ($relations) {
                foreach ($relations as $i => $relation) {
                    $relation['modelNamespace'] = $data['modelNamespace'];
                    if (!is_file($relation['relationFile'])) {
                        // 生成关联模型文件
                        $result = $this->writeToFile('relationmodel', $relation, $relation['relationFile']);
                    }
                }

            }
            // 生成视图文件
            $result = $this->writeToFile('add', $data, $addFile);
            $result = $this->writeToFile('edit', $data, $editFile);
            $result = $this->writeToFile('index', $data, $indexFile);
            // 生成JS文件
            $result = $this->writeToFile('javascript', $data, $javascriptFile);
        } catch (\think\exception\ErrorException $e) {
            throw new Exception("Code: " . $e->getCode() . "\nLine: " . $e->getLine() . "\nMessage: " . $e->getMessage() . "\nFile: " . $e->getFile());
        }
        //输出提示
        $output->info("Build Successed");
    }

    /**
     * 写入到文件
     * @param [String] $name [文件模版名字]
     * @param [Array] $data  [文件模版]
     * @param [String] $pathname [文件路径名字]
     */
    protected function writeToFile($name, $data, $pathname)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $content = $this->getReplacedTpl($name, $data);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }

    /**
     * 获取替换后的模版内容
     * @param [String] $name  [模版名字]
     * @param [Array] $data  [替换数据]
     * @return [String] [替换数据后的模版]
     */
    protected function getReplacedTpl($name, $data)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = "{%{$k}%}";
            $replace[] = $v;
        }
        $stubname = $this->getTemplate($name);
        if (isset($this->stubList[$stubname])) {
            $stub = $this->stubList[$stubname];
        } else {
            $this->stubList[$stubname] = $stub = file_get_contents($stubname);
        }
        $content = str_replace($search, $replace, $stub);
        return $content;
    }

    /**
     * 获取基础模板
     * @param [String] $name [模版名字]
     * @return [String] [返回模版路径]
     */
    protected function getTemplate($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Crud' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $name . '.tpl';
    }

    /**
     * 生成模型名称，如果未传入自定义模型名称，则以表名命名
     * @param  [String] $model [自定义模型名称]
     * @param  [String] $table [表名]
     * @return [String]        [生成的模型名称]
     */
    protected function getModelName($model, $table)
    {
        if (!$model) {
            $modelarr = explode('_', strtolower($table));
            foreach ($modelarr as $k => &$v) {
                $v = ucfirst($v);
            }

            unset($v);
            $modelName = implode('', $modelarr);
        } else {
            $modelName = ucfirst($model);
        }
        return $modelName;
    }

    /**
     * 将数据转换成带字符串
     * @param [Array] $arr [数组]
     * @return [String] [转换后的字符串]
     */
    protected function getArrayString($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        $stringArr = [];
        foreach ($arr as $k => $v) {
            $is_var = in_array(substr($v, 0, 1), ['$', '_']);
            if (!$is_var) {
                $v = str_replace("'", "\'", $v);
                $k = str_replace("'", "\'", $k);
            }
            $stringArr[] = "'" . $k . "' => " . ($is_var ? $v : "'{$v}'");
        }
        return implode(",", $stringArr);
    }

    /**
     * 获取某项的信息
     * @param  [Array] $item    [item数组]
     * @param  [String] $field   [字段名称]
     * @param  [String] $comment [描述]
     * @return [Array]          [构造后的item新数组]
     */
    protected function getItemArray($item, $field, $comment)
    {
        $itemArr = [];
        $comment = str_replace('，', ',', $comment);
        if (stripos($comment, ':') !== false && stripos($comment, ',') && stripos($comment, '=') !== false) {
            list($fieldLang, $item) = explode(':', $comment);
            $itemArr = [];
            foreach (explode(',', $item) as $k => $v) {
                $valArr = explode('=', $v);
                if (count($valArr) == 2) {
                    list($key, $value) = $valArr;
                    $itemArr[$key] = $value;
                }
            }
            //写入字典配置，字典配置中没有相同配置则插入新配置
            $dataE = Db::table('fa_config')->where(['name' => $field])->find();
            if (!$dataE) {
                Db::table('fa_config')->insert(['name' => $field, 'group' => 'dictionary', 'title' => $fieldLang, 'type' => 'array', 'value' => json_encode($itemArr, 256)]);
                //刷新配置文件
                refreshFile();
            }
        } else {
            foreach ($item as $k => $v) {
                $itemArr[$v] = is_numeric($v) ? $field . ' ' . $v : $v;
            }
        }
        return $itemArr;
    }

    /**
     * 获取字段类型
     * @param  [Array] $field [字段信息]
     * @return [String]     [字段类型]
     */
    protected function getFieldType(&$field)
    {
        $inputType = 'text';
        switch ($field['DATA_TYPE']) {
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
                $inputType = 'number';
                break;
            case 'enum':
            case 'set':
                $inputType = 'select';
                break;
            case 'decimal':
            case 'double':
            case 'float':
                $inputType = 'number';
                break;
            case 'longtext':
            case 'text':
            case 'mediumtext':
            case 'smalltext':
            case 'tinytext':
                $inputType = 'textarea';
                break;
            case 'year';
            case 'date';
            case 'time';
            case 'datetime';
            case 'timestamp';
                $inputType = 'datetime';
                break;
            default:
                break;
        }
        $fieldsName = $field['COLUMN_NAME'];
        // 指定后缀说明也是个时间字段
        if ($this->isMatchSuffix($fieldsName, $this->intDateSuffix)) {
            $inputType = 'datetime';
        }
        // 指定后缀结尾且类型为enum,说明是个单选框
        if ($this->isMatchSuffix($fieldsName, $this->enumRadioSuffix) && $field['DATA_TYPE'] == 'enum') {
            $inputType = "radio";
        }
        // 指定后缀结尾且类型为set,说明是个复选框
        if ($this->isMatchSuffix($fieldsName, $this->setCheckboxSuffix) && $field['DATA_TYPE'] == 'set') {
            $inputType = "checkbox";
        }
        // 指定后缀结尾且类型为char或tinyint且长度为1,说明是个Switch复选框
        if ($this->isMatchSuffix($fieldsName, $this->switchSuffix) && ($field['COLUMN_TYPE'] == 'tinyint(1)' || $v['COLUMN_TYPE'] == 'char(1)') && $v['COLUMN_DEFAULT'] !== '' && $v['COLUMN_DEFAULT'] !== null) {
            $inputType = "switch";
        }
        // 指定后缀结尾城市选择框
        if ($this->isMatchSuffix($fieldsName, $this->citySuffix) && ($field['DATA_TYPE'] == 'varchar' || $v['DATA_TYPE'] == 'char')) {
            $inputType = "citypicker";
        }
        return $inputType;
    }

    /**
     * 判断是否符合指定后缀
     * @param [String] $field [字段名称]
     * @param [String] $suffixArr [后缀]
     * @return [Boolean] [返回布尔值]
     */
    protected function isMatchSuffix($field, $suffixArr)
    {
        $suffixArr = is_array($suffixArr) ? $suffixArr : explode(',', $suffixArr);
        foreach ($suffixArr as $k => $v) {
            if (preg_match("/{$v}$/i", $field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取表单分组数据
     * @param [String] $field [字段名称]
     * @param [String] $field_comment [字段描述]
     * @param [String] $content [html代码]
     * @return [String] [返回完整的html代码]
     */
    protected function getFormGroup($field, $field_comment, $content)
    {
        $langField = mb_ucfirst($field);
        if (stripos($field_comment, ':') !== false && stripos($field_comment, ',') && stripos($field_comment, '=') !== false) {
            $field_comment = explode(':', $field_comment);
            $field_comment = $field_comment[0];
        }
        return <<<EOT
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{$field_comment}:</label>
        <div class="col-xs-12 col-sm-8">
            {$content}
        </div>
    </div>
EOT;
    }

    /**
     * 获取图片模板数据
     * @param [String] $field [字段名称]
     * @param [String] $content [字段html代码]
     * @return [String] [返回完整的图片上传html代码]
     */
    protected function getImageUpload($field, $content)
    {
        $uploadfilter = $selectfilter = '';
        if ($this->isMatchSuffix($field, $this->imageField)) {
            $uploadfilter = ' data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp"';
            $selectfilter = ' data-mimetype="image/*"';
        }
        $multiple = substr($field, -1) == 's' ? ' data-multiple="true"' : ' data-multiple="false"';
        $preview = $uploadfilter ? ' data-preview-id="p-' . $field . '"' : '';
        $previewcontainer = $preview ? '<ul class="row list-inline plupload-preview" id="p-' . $field . '"></ul>' : '';
        return <<<EOD
<div class="input-group">
                {$content}
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-{$field}" class="btn btn-danger plupload" data-input-id="c-{$field}"{$uploadfilter}{$multiple}{$preview}><i class="fa fa-upload"></i> 上传</button></span>
                </div>
                <span class="msg-box n-right" for="c-{$field}"></span>
            </div>
            {$previewcontainer}
EOD;
    }

    protected function getEnum(&$getEnum, &$controllerAssignList, $field, $itemArr = '', $inputType = '')
    {
        if (!in_array($inputType, ['datetime', 'select', 'multiple', 'checkbox', 'radio'])) {
            return;
        }

        $fieldList = $this->getFieldListName($field);
        $methodName = 'get' . ucfirst($fieldList);
        foreach ($itemArr as $k => &$v) {
            $v = mb_ucfirst($v);
        }
        unset($v);
        //$itemString = $this->getArrayString($itemArr);
        $getEnum[] = <<<EOT
    public function {$methodName}()
    {
        return config('site.$field');
    }
EOT;
        $controllerAssignList[] = <<<EOT
                \$this->view->assign("{$fieldList}", \$this->model->{$methodName}());
EOT;
    }

    protected function getAttr(&$getAttr, $field, $inputType = '')
    {
        if (!in_array($inputType, ['datetime', 'select', 'multiple', 'checkbox', 'radio'])) {
            return;
        }

        $attrField = ucfirst($this->getCamelizeName($field));
        $getAttr[] = $this->getReplacedTpl("mixins" . DIRECTORY_SEPARATOR . $inputType, ['field' => $field, 'methodName' => "get{$attrField}TextAttr", 'listMethodName' => "get{$attrField}List"]);
    }

    protected function setAttr(&$setAttr, $field, $inputType = '')
    {
        if (!in_array($inputType, ['datetime', 'checkbox', 'select'])) {
            return;
        }

        $attrField = ucfirst($this->getCamelizeName($field));
        if ($inputType == 'datetime') {
            $return = <<<EOT
return \$value && !is_numeric(\$value) ? strtotime(\$value) : \$value;
EOT;
        } else if (in_array($inputType, ['checkbox', 'select'])) {
            $return = <<<EOT
return is_array(\$value) ? implode(',', \$value) : \$value;
EOT;
        }
        $setAttr[] = <<<EOT
    protected function set{$attrField}Attr(\$value)
    {
        $return
    }
EOT;
    }

    protected function appendAttr(&$appendAttrList, $field)
    {
        $appendAttrList[] = <<<EOT
        '{$field}_text'
EOT;
    }

    /**
     * 获取JS列数据
     * @param [String] $field [字段名称]
     * @param [String] $fieldComment [字段描述]
     * @param [String] $datatype [字段类型]
     * @param [Int] $num [字段序号]
     * @return [String] [返回字段的js代码]
     */
    protected function getJsColumn($field, $fieldComment, $datatype = '', $num)
    {
        $formatter = '';
        foreach ($this->fieldFormatterSuffix as $k => $v) {
            if (preg_match("/{$k}$/i", $field)) {
                if (is_array($v)) {
                    if (in_array($datatype, $v['type'])) {
                        $formatter = $v['name'];
                        break;
                    }
                } else {
                    $formatter = $v;
                    break;
                }
            }
        }
        //判断是否为选项字段，如果是则取冒号前的描述
        if (stripos($fieldComment, ':') !== false && stripos($fieldComment, ',') && stripos($fieldComment, '=') !== false) {
            $fieldComment = explode(':', $fieldComment);
            $fieldComment = $fieldComment[0];
            $field = $field . '_text';
        }
        $html = ($num == 1 ? '' : str_repeat(" ", 20)) . "{ 'needControlShow':true, data: '{$field}', title: '{$fieldComment}'";
        if ($formatter) {
            $html .= ",
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter." . $formatter . "(data)
                        }
                    }";
        } else {
            $html .= "}";
        }
        return $html;
    }

    /**
     * 转换驼峰名称
     * @param  [String] $uncamelized_words [未转换驼峰的词]
     * @param  string $separator         [分隔符]
     * @return [String]                    [转换后的词]
     */
    protected function getCamelizeName($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    /**
     * 获取字段列表名称
     * @param  [String] $field [字段名称]
     * @return [String]        [转换后的字段名称]
     */
    protected function getFieldListName($field)
    {
        return $this->getCamelizeName($field) . 'List';
    }
}
