<?php

/**
 * 路由异常类
 */
class Router_Exception extends Component_Exception {

    /**
     * 没有找到对应的 controller
     */
    const E_CONTROLLER_NOT_FOUND = 201;

    /**
     * 路由定向失败
     */
    const E_RENDER_ERROR = 202;

    /**
     * 非法的参数
     */
    const E_BAD_ACTION_PARAM = 203;
}