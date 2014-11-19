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
     * 获取 DAO 实例
     * 统一保存，避免反复实例化
     * @param <type> $engine
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
}
