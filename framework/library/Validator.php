<?php
/**
 * 验证类
 *
 */

class Validator {

    /**
     * 是否已初始化
     */
    private static $_inited = false;

    /**
     * 验证失败错误信息
     */
    private static $_errMsg = '';

    /**
     * 验证规则
     */
    private static $_rules = array();

    /**
     * 初始化验证规则
     */
    private static function _init() {

        if (self::$_inited) {
            return;
        }
        self::$_inited = true;

        // 内置
        self::$_rules = array(
            'email' => Validator_Rule::factory('regexp', '/^[_\\.0-9a-z-]+@([0-9a-z][0-9a-z-]*\\.)+[a-z0-9]{2,}$/i'),
            'password' => Validator_Rule::factory('regexp', '/^[\x21-\x7e ]{2,32}$/'),
            'username' => Validator_Rule::factory('regexp', "/^[^'\,\"%*\n\r\t?<>\\/\\\\ ]{2,32}$/"),
            'mobile' => Validator_Rule::factory('regexp', '/^1[2-9][0-9]{9}$/'),
            'url' => Validator_Rule::factory('regexp', '/(http:\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/'),
        );

        // 配置
        $rules = V::config('validator.rules');
        if ($rules && is_array($rules)) {
            try{
                foreach ($rules as $ruleId => $rule) {
                    if (is_array($rule) && isset($rule['type']) && isset($rule['option'])) {
                        $rule = Validator_Rule::factory($rule['type'], $rule['option']);
                    }
                    if ($rule instanceof Validator_Rule_Abstract) {
                        self::setRule($ruleId, $rule);
                    }
                }
            } catch (Exception $e) {
                throw new Validator_Exception('Init validator failed: '.$e->getMessage(), Validator_Exception::E_VALIDATOR_RULE_INIT_ERROR);
            }
        }

        // 自动加载
        include dirname(__FILE__) . '/Validator/functions.php';
    }

    /**
     * 设置验证规则
     * @param <type> $ruleId
     * @param Validator_Rule_Abstract $validatorRule
     */
    public static function setRule($ruleId, Validator_Rule_Abstract $validatorRule) {

        self::$_rules[$ruleId] = $validatorRule;
    }

    /**
     * 验证
     */
    public static function validate() {

        self::$_errMsg = '';

        // 初始化配置验证规则
        if (false == self::$_inited) {
            self::_init();
        }

        if (func_num_args() < 2) {
            return false;
        }
        $ruleId = func_get_arg(0);
        if (!isset(self::$_rules[$ruleId])) {

            // 先尝试自动加载验证函数； 若还是没有对应规则，抛出异常
            if (function_exists('validator_' . $ruleId)) {
                self::setRule($ruleId, Validator_Rule::factory('callback', 'validator_' . $ruleId));
            } else {
                throw new Validator_Exception('No such Validator_Rule: ' . $ruleId, Validator_Exception::E_VALIDATOR_RULE_NOT_FOUND);
            }
        }

        try {
            $param = func_get_args();
            array_shift($param);
            return call_user_func_array(array(self::$_rules[$ruleId], 'check'), $param);
        } catch (Exception $e) {
            throw new Validator_Exception('validate error: '.$e->getMessage(), Validator_Exception::E_VALIDATOR_ERROR);
        }
    }

    /**
     * 设置错误信息
     * @param <type> $errMsg
     */
    public static function setErrorMessage($errMsg) {

        self::$_errMsg = $errMsg;
    }

    /**
     * 获取错误提示信息
     * @return <type>
     */
    public static function getErrorMessage() {

        return self::$_errMsg;
    }
}