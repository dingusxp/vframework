<?php

/**
 * 验证规则类：基类
 */
abstract class Validator_Rule_Abstract {
    
    /**
     * 配置信息
     * @var type 
     */
    protected $_option = array();

    /**
     * 构造函数
     * @param type $option
     */
    public function  __construct($option) {

        $this->_option = $option;
    }

    /**
     * 检验方法
     */
    abstract function check();
}