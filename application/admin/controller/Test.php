<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 测试文章管理
 *
 */
class Test extends Backend
{

    /**
     * Test模型对象
     * @var \app\admin\model\Test
     */
    protected $model = null;

    public function initialize()
    {
        parent::initialize();
        $this->model = model('Test');
                $this->view->assign("weekList", $this->model->getWeekList());
                $this->view->assign("sexdataList", $this->model->getSexdataList());
                $this->view->assign("attrdataList", $this->model->getAttrdataList());
    }

    
}