<?php

/**
 * 验证规则
 */
class Validator_Rule {

    /**
     * 工厂
     * @param type $type
     * @param type $option
     * @return \Validator_Rule_Abstract
     * @throws Validator_Exception
     */
    public static function factory($type, $option) {

        $class = 'Validator_Rule_' . ucfirst($type);
        try {
            if (class_exists($class)) {
                $validatorRule = new $class($option);
                if ($validatorRule instanceof Validator_Rule_Abstract) {
                    return $validatorRule;
                }
            }
            
            throw new Validator_Exception('Validator rule does not exist: ' . $type, Validator_Exception::E_VALIDATOR_RULE_INIT_ERROR);
        } catch (Exception $e) {
            throw new Validator_Exception($e);
        }
    }
}