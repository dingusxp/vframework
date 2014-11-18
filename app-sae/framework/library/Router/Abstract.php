<?php

/**
 * 路由器 抽象类
 */
abstract class Router_Abstract {

    /**
     * option
     * @var <type>
     */
    protected $_option = array();

    /**
     * __construct
     * 构造函数，进行路由解析，获得 controller， action 和链接参数
     */
    public function  __construct($option = array()) {

        if ($option && is_array($option)) {
            $this->_option = $option;
        }
    }

    abstract public function parse();
}
