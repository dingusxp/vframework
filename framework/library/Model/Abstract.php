<?php

/**
 * Model 基类
 */
abstract class Model_Abstract {

    /**
     * 方法映射
     * 当 Model 方法较多时，通过配置映射来将代码分散到多个文件而不用修改调用点
     * @var array
     */
    protected static $_methodMapping = array();

    /**
     * 魔术方法 映射
     */
    public function __call($method, $param) {

		if (isset(self::$_methodMapping[$method])) {
			return call_user_func_array(self::$_methodMapping[$method], $param);
        }
		trigger_error('call an unknown method: '.__CLASS__.'::'.$method, E_USER_ERROR);
    }
}