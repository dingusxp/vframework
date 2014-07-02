<?php

/**
 * 回调类验证规则
 */
class Validator_Rule_Callback extends Validator_Rule_Abstract {

    /**
     * 回调函数
     * @var type 
     */
    private $_callback;
    
    /**
     * 构造函数
     * @param array $option
     * @throws Validator_Exception
     */
    public function  __construct($option) {

        // 参数必须是合法的函数
        if (is_string($option)) {
            if (!function_exists($option)) {
                throw new Validator_Exception('Validator_Rule_Callback need a function for constructor', Validator_Exception::E_PARAM_ERROR);
            }
        } elseif (is_array($option)) {
            list($object, $method) = $option;
            if (!method_exists($object, $method)) {
                throw new Validator_Exception('Validator_Rule_Callback need a function for constructor', Validator_Exception::E_PARAM_ERROR);
            }
        } else {
            throw new Validator_Exception('Validator_Rule_Callback need a function for constructor', Validator_Exception::E_PARAM_ERROR);
        }
        
        $this->_callback = $option;
        $option = array('callback' => $option);
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
        $arguments = func_get_args();
        return call_user_func_array($this->_callback, $arguments) ? true : false;
    }
}