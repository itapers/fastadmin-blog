<?php
/*
 * @Author: hippo
 * @Date: 2018-02-05 14:32:07
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-31 10:40:41
 */

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use Env;
use Exception;

class System extends Backend
{
    protected $model = null;
    protected $noNeedRight = ['check'];

    public function initialize()
    {
        parent::initialize();
        $this->model = model('Config');
    }

    public function index()
    {
        $siteList = [];
        $groupList = ConfigModel::getGroupList();
        foreach ($groupList as $k => $v) {
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }
        $all = $this->model->all()->toArray();
        foreach ($all as $k => $v) {
            if (!isset($siteList[$v['group']])) {
                continue;
            }
            $value = $v;
            $value['title'] = $value['title'];
            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array') {
                $value['value'] = (array) json_decode($value['value'], true);
            }
            $value['content'] = json_decode($value['content'], true);
            $siteList[$v['group']]['list'][] = $value;
        }
        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false;
            $index++;
        }
        $this->view->assign('siteList', $siteList);
        $this->view->assign('typeList', ConfigModel::getTypeList());
        $this->view->assign('groupList', ConfigModel::getGroupList());
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                foreach ($params as $k => &$v) {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try
                {
                    if (in_array($params['type'], ['select', 'selects', 'checkbox', 'radio', 'array'])) {
                        //JSON_UNESCAPED_UNICODE：中文不转为unicode
                        $params['content'] = json_encode(ConfigModel::decode($params['content']), JSON_UNESCAPED_UNICODE);
                    } else {
                        $params['content'] = '';
                    }
                    $result = $this->model->create($params);
                    if ($result !== false) {
                        try
                        {
                            $this->refreshFile();
                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                        }
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error('参数不能为空');
        }
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $row = $this->request->post("row/a");
            if ($row) {
                $configList = [];
                foreach ($this->model->all() as $v) {
                    if (isset($row[$v['name']])) {
                        $value = $row[$v['name']];
                        if (is_array($value) && isset($value['field'])) {
                            $value = json_encode(ConfigModel::getArrayData($value), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;
                        $configList[] = $v->toArray();
                    }
                }
                $this->model->allowField(true)->saveAll($configList);
                try
                {
                    $this->refreshFile();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error('参数不能为空');
        }
    }

    protected function refreshFile()
    {
        $config = [];
        foreach ($this->model->all() as $k => $v) {

            $value = $v->toArray();
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

    /**
     * @internal
     */
    public function check()
    {
        $params = $this->request->post("row/a");
        if ($params) {

            $config = $this->model->get($params);
            if (!$config) {
                return $this->success();
            } else {
                return $this->error('变量名称已经存在');
            }
        } else {
            return $this->error('未知参数');
        }
    }

    /**
     * 发送测试邮件
     * @internal
     */
    public function emailtest()
    {
        $receiver = $this->request->request("receiver");
        $email = new Email;
        $result = $email
            ->to($receiver)
            ->subject('这是一封测试邮件')
            ->message('<div style="min-height:550px; padding: 100px 55px 200px;">这是一封测试邮件,用于测试邮件配置是否正常!</div>')
            ->send();
        if ($result) {
            $this->success();
        } else {
            $this->error($email->getError());
        }
    }
}
