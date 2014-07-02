<?php
/**
 * 验证类异常
 *
 */

class Validator_Exception extends V_Exception {

    /**
     * 初始化规则失败
     *
     */
    const E_VALIDATOR_RULE_INIT_ERROR = 101;

    /**
     * 没有对应的验证规则
     */
    const E_VALIDATOR_RULE_NOT_FOUND = 102;

    /**
     * 验证失败
     */
    const E_VALIDATOR_ERROR = 103;

}