<?php

namespace app\common\command\Api\library;

use Config;
use think\Container;
use View;

/**
 * @website https://github.com/calinrada/php-apidoc
 * @author  Calin Rada <rada.calin@gmail.com>
 * @author  Karson <karsonzhang@163.com>
 */
class Builder
{

    /**
     *
     * @var \think\View
     */
    public $view = null;

    /**
     * parse classes
     * @var array
     */
    protected $classes = [];

    /**
     *
     * @param array $classes
     */
    public function __construct($classes = [])
    {
        $this->classes = array_merge($this->classes, $classes);
        $this->view = View::init(Config::pull('template'), Config::pull('view_replace_str'));
    }

    protected function extractAnnotations()
    {
        $st_output = [];
        foreach ($this->classes as $class) {
            $st_output[] = Extractor::getAllClassAnnotations($class);
        }
        return end($st_output);
    }

    protected function generateHeadersTemplate($docs)
    {
        if (!isset($docs['headers'])) {
            return [];
        }

        $headerslist = array();
        foreach ($docs['headers'] as $params) {
            $tr = array(
                'name' => $params['name'],
                'type' => $params['type'],
                'sample' => isset($params['sample']) ? $params['sample'] : '',
                'require' => isset($params['require']) && $params['require'] ? '<font color="red">必须</font>' : '可选',
                'desc' => isset($params['desc']) ? $params['desc'] : '',
            );
            $headerslist[] = $tr;
        }

        return $headerslist;
    }

    protected function generateParamsTemplate($docs)
    {
        if (!isset($docs['params'])) {
            return [];
        }

        $paramslist = array();
        foreach ($docs['params'] as $params) {
            $tr = array(
                'name' => $params['name'],
                'type' => isset($params['type']) ? $params['type'] : 'string',
                'sample' => isset($params['sample']) ? $params['sample'] : '',
                'require' => isset($params['require']) && $params['require'] ? '<font color="red">必须</font>' : '可选',
                'desc' => isset($params['desc']) ? $params['desc'] : '',
            );
            $paramslist[] = $tr;
        }

        return $paramslist;
    }

    protected function generateReturnHeadersTemplate($docs)
    {
        if (!isset($docs['returnheaders'])) {
            return [];
        }

        $headerslist = array();
        foreach ($docs['returnheaders'] as $params) {
            $tr = array(
                'name' => $params['name'],
                'type' => 'string',
                'sample' => isset($params['sample']) ? $params['sample'] : '',
                'require' => isset($params['require']) && $params['require'] ? '<font color="red">必须</font>' : '可选',
                'desc' => isset($params['desc']) ? $params['desc'] : '',
            );
            $headerslist[] = $tr;
        }

        return $headerslist;
    }

    protected function generateReturnParamsTemplate($st_params)
    {
        if (!isset($st_params['return'])) {
            return [];
        }

        $paramslist = array();
        foreach ($st_params['return'] as $params) {
            $tr = array(
                'name' => $params['name'],
                'type' => isset($params['type']) ? $params['type'] : 'string',
                'sample' => isset($params['sample']) ? $params['sample'] : '',
                'require' => isset($params['require']) && $params['require'] ? '<font color="red">必须</font>' : '可选',
                'desc' => isset($params['desc']) ? $params['desc'] : '',
            );
            $paramslist[] = $tr;
        }

        return $paramslist;
    }

    protected function generateBadgeForMethod($data)
    {
        $method = strtoupper(is_array($data['method'][0]) ? $data['method'][0]['data'] : $data['method'][0]);
        $labes = array(
            'POST' => 'label-primary',
            'GET' => 'label-success',
            'PUT' => 'label-warning',
            'DELETE' => 'label-danger',
            'PATCH' => 'label-default',
            'OPTIONS' => 'label-info',
        );

        return isset($labes[$method]) ? $labes[$method] : $labes['GET'];
    }

    public function parse()
    {
        $annotations = $this->extractAnnotations();

        $counter = 0;
        $section = null;
        $docslist = [];
        foreach ($annotations as $class => $methods) {
            foreach ($methods as $name => $docs) {
                $section = $class;
                if (0 === count($docs)) {
                    continue;
                }
                $docslist[$section][] = [
                    'id' => $counter,
                    'title' => is_array($docs['title'][0]) ? $docs['title'][0]['data'] : $docs['title'][0],
                    'method' => is_array($docs['method'][0]) ? $docs['method'][0]['data'] : $docs['method'][0],
                    'section' => $section,
                    'route' => is_array($docs['route'][0]) ? $docs['route'][0]['data'] : $docs['route'][0],
                    'desc' => is_array($docs['desc'][0]) ? $docs['desc'][0]['data'] : $docs['desc'][0],
                    'headers' => $this->generateHeadersTemplate($docs),
                    'params' => $this->generateParamsTemplate($docs),
                    'return' => $this->generateReturnParamsTemplate($docs),
                ];
                $counter++;
            }
        }
        // print_r($docslist);die;
        return $docslist;
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     * 渲染
     * @param string $template
     * @param array $vars
     * @return string
     */
    public function render($template, $vars = [])
    {
        $docslist = $this->parse();

        return $this->view->display(file_get_contents($template), array_merge($vars, ['docslist' => $docslist]));
    }

}
