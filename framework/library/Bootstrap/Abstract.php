<?php

/**
 * Bootstrap_Abstract
 * 程序引导类
 */
abstract class Bootstrap_Abstract {

    /**
     * 配置项
     */
    protected $_option = array();

    /**
     * 构造函数
     */
    public function  __construct(array $option = array()) {

        $this->_option = $option;
    }

    /**
     * 运行
     */
    abstract public function run();
}
