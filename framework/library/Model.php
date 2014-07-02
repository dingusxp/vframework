<?php

/**
 * Model 类
 */
class Model extends Component {

    /**
     * DAO 实例
     * @var <type>
     */
    private static $_daoInstances = array();
    
    /**
     * Model 实例
     * @var type 
     */
    private static $_modelInstances = array();

    /**
     * 方法映射
     * 当 Model 方法较多时，用来将代码分散到多个文件
     * @var array
     */
    protected static $_methodMapping = array();

    /**
     * 工厂 （建议使用 getInstance，减少实例生成）
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
    
    /**
     * 获取 model 实例
     */
    public static function getInstance($engine) {

        if (!isset(self::$_modelInstances[$engine])) {
            try {
                self::$_modelInstances[$engine] = self::factory($engine);
            } catch (Exception $e) {
                throw new Model_Exception(V::t('Init model({name}) failed: {msg}', array('name' => $engine, 'msg' => $e->getMessage()), 'framework'),
                    Model_Exception::E_COMPONENT_INIT_FAILED);
            }
        }

        return self::$_modelInstances[$engine];
    }

    /**
     * 获取 DAO 实例
     * 统一保存，避免反复实例化
     * @param <type> $name
     */
    public static function getDAO($engine) {

        if (!isset(self::$_daoInstances[$engine])) {
            try {
                self::$_daoInstances[$engine] = DAO::factory($engine);
            } catch (Exception $e) {
                throw new Model_Exception(V::t('Init dao({name}) failed: {msg}', array('name' => $engine, 'msg' => $e->getMessage()), 'framework'),
                    Model_Exception::E_MODEL_INIT_DAO_ERROR);
            }
        }

        return self::$_daoInstances[$engine];
    }

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
