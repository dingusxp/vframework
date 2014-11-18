<?php

/**
 * 组件异常类
 */
class Component_Exception extends V_Exception {

    /**
     * 指定加载的组件不存在
     */
    const E_COMPONENT_NOT_EXIST = 101;

    /**
     * 加载组件失败
     */
    const E_COMPONENT_INIT_FAILED = 102;
}