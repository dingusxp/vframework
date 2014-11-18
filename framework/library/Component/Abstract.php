<?php

/**
 * 组件，基类
 */
abstract class Component_Abstract {
    
    /**
     * 参数信息
     * @var type 
     */
    protected $_option = array();
    
    /**
     * 构造函数
     * @param type $option
     */
    public function __construct($option = array()) {
        
        $this->_option = $option;
    }
    
}