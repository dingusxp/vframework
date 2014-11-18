<?php

/**
 * 简易路由
 * 直接由变量指定 controller 和 action
 */
class Router_Simple extends Router_Abstract {
    
    /**
     * _parse
     * 路由解析
     */
    public function  parse() {

        // 路由解析
        $option = $this->_option;
        $key = isset($option['action_key']) ? $option['action_key'] : 'r';
        $sep = isset($option['action_seperater']) ? $option['action_seperater'] : '/';

        // 解析 controller 和 action
        if (empty($_GET[$key]) || false == Str::has($_GET[$key], $sep)) {
            return !empty($option['default_action']) ? $option['default_action'] : array();
        }

        // 没有或格式不合
        list($controller, $action) = explode($sep, $_GET[$key]);
        if (!$controller || !$action) {
            return array();
        }

        // 限制了对外可以访问的 controller
        if ($option['allow_controllers'] && !in_array($controller, $option['allow_controllers'])) {
            return array();
        }

        return array(
            'operation' => 'action',
            'param' => array(
                'controller' => $controller,
                'action' => $action
                )
            );
    }
}