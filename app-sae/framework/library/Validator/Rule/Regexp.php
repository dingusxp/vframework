<?php

/**
 * 正则类验证规则
 */
class Validator_Rule_Regexp extends Validator_Rule_Abstract {
    
    /**
     * 构造函数
     * @param type $option
     * @throws Validator_Exception
     */
    public function  __construct($option) {

        if (is_string($option)) {
            $option = array('regexp' => $option);
        } elseif(!is_array($option) || !isset ($option['regexp'])) {
            throw new Validator_Exception('Validator_Rule_Regexp need a regexp for constructor', Validator_Exception::E_PARAM_ERROR);
        }
        
        parent::__construct($option);
    }

    /**
     * 检验
     * @param <type> $var
     */
    public function check() {

        if (func_num_args() < 1) {
            return false;
        }
        
        $var = func_get_arg(0);
        return preg_match($this->_option['regexp'], $var);
    }
}