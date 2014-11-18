<?php

/**
 * web 引导器（MVC 方式）
 */
class Bootstrap_Web extends Bootstrap_Abstract {

    /**
     * run
     * Router 解析 & render
     */
    public function run() {

        // 路由解析
        try {
            $configKey = V::config('router_key');
            $router = Router::factory(V::config($configKey, array()));
            $action = $router->parse();
        } catch (Router_Exception $e) {
            return Router::renderError500($e);
        }
        if (!$action) {
            return Router::renderError404();
        }

        return Router::renderAction($action);
    }
}
