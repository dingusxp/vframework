<?php

/**
 * Model 异常
 */
class Model_Exception extends Component_Exception {

    /**
     * 获取指定DAO失败
     */
    const E_MODEL_INIT_DAO_ERROR = 301;

    /**
     * DAO 操作失败
     */
    const E_MODEL_DAO_OPERATION_ERROR = 302;
    
    /**
     * 没有权限进行操作
     */
    const E_MODEL_ACCESS_DENIED = 303;
}