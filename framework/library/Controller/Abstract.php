<?php

/**
 * 控制器抽象类
 */
abstract class Controller_Abstract {

    /**
     * 模板引擎
     * @var <type>
     */
    protected $_view;

    /**
     * request 实例
     * @var <type>
     */
    protected $_request;

    /**
     * response 实例
     * @var <type>
     */
    protected $_response;

    /**
     * session 实例
     * @var <type>
     */
    protected $_session;

    /**
     * 实例类的名称
     * @var <type>
     */
    protected $_controllerName;

    /**
     * 执行的 action
     * @var <type>
     */
    public $_actionName;

    /**
     * 构造函数
     */
    public function  __construct() {

        // 判断是否 Module
        $class = get_class($this);
        $this->_controllerName = substr($class, 11);// 11 is the length of "Controller[_\]"

        $this->_request = Web_Request::getInstance();
        $this->_response = Web_Response::getInstance();
        $this->_session = Web_Session::getInstance();

        // view
        $config = V::config('web.view');
        if (!$config) {
            $config = V::config('view');
        }
        $this->_view = View::factory($config);
    }

    /**
     * 执行动作前调用，若返回 false，则终止动作
     * @return <type>
     */
    protected function _beforeAction() {

        return true;
    }

    /**
     * 执行指定动作
     * @param <type> $action
     */
    public function doAction($action) {

        $this->_actionName = $action;
        if ($this->_beforeAction()) {
            $method = 'action' . $action;
            $this->$method();
            $this->_afterAction();
        }

        $this->_response->output();
    }

    /**
     * 执行动作后的调用
     */
    protected function _afterAction() {

    }
}