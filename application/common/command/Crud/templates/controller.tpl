<?php

namespace {%controllerNamespace%};

use app\common\controller\Backend;

/**
 * {%tableComment%}
 *
 */
class {%controllerName%} extends Backend
{

    /**
     * {%modelName%}模型对象
     * @var \{%modelNamespace%}\{%modelName%}
     */
    protected $model = null;

    public function initialize()
    {
        parent::initialize();
        $this->model = model('{%modelName%}');
{%controllerAssignList%}
    }

    {%controllerIndex%}
}