<?php

/**
 * 路由解析
 */
class Router extends Component {

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }

    /**
     * 定向到 控制器的 Action
     * @param <type> $controller
     * @param <type> $action
     */
    public static function renderAction($action) {

        try {
            if ($action['operation'] == 'action') {
                return self::_operationAction($action['param']);
            } elseif ($action['operation'] == 'view') {
                return self::_operationView($action['param']);
            } elseif ($action['operation'] == 'redirect') {
                return self::_operationRedirect($action['param']);
            }
        } catch (Exception $e) {
            return self::renderError500($e);
        }

        return self::renderError404();
    }

    /**
     * 重定向
     * @param <type> $param
     */
    private static function _operationRedirect($param) {

        $url = $param['url'];
        $isPermanent = !empty($param['is_permanent']) ? true : false;
        $response = Web_Response::getInstance();
        $response->redirect($url, $isPermanent);
        $response->output();
    }

    /**
     * 用视图渲染输出
     * @param <type> $param
     */
    private static function _operationView($param) {

        $view = View::factory($param);
        $view->render($param['template']);
        Web_Response::getInstance()->output();
    }

    /**
     * 执行指定 controller 的 action
     * @param <type> $param
     */
    private static function _operationAction($param) {

        // 检查参数
        if (!preg_match('/\w{1,64}/', $param['controller']) || !preg_match('/\w{1,64}/', $param['action'])) {
            return self::renderError404();
        }

        // 获取 controller 组件
        try {
            $controller = Controller::factory($param['controller']);
        } catch (Component_Exception $e) {
            if ($e->getCode() == Component_Exception::E_COMPONENT_NOT_EXIST) {
                return self::renderError404();
            }
            return self::renderError500($e);
        } catch (Exception $e) {
            return self::renderError500($e);
        }

        // 执行方法
        $actionName = ucfirst($param['action']);
        if (!method_exists($controller, 'action' . $actionName)) {
            return self::renderError404();
        }

        return $controller->doAction($actionName);
    }

    /**
     * 输出文件内容
     * @param <type> $url
     */
    public static function renderError404() {

        // log error
        $errMsg = '404 url:'.Web_Request::getInstance()->url();
        V::log($errMsg, Logger::LEVEL_NOTICE);

        Web_Response::getInstance()->setResponseCode(404);
		$configKey = V::config('router_key');
		$config = V::config($configKey, array());
        self::renderAction($config['on_error_404']);
    }

    /**
     * 内部错误输出
     * @param <type> $errMsg
     */
    public static function renderError500($errMsg) {

        // log error
        V::log($errMsg, Logger::LEVEL_ERROR);

        Web_Response::getInstance()->setResponseCode(500);
		$configKey = V::config('router_key');
		$config = V::config($configKey, array());
        self::renderAction($config['on_error_500']);
    }
}
