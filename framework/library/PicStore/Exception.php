<?php

/**
 * picstore 异常类
 */
class PicStore_Exception extends V_Exception {

    /**
     * 初始化组件失败
     */
    const E_PICSTORE_LOAD_COMPONENT_FAILED = 101;

    /**
     * storage 操作失败
     */
    const E_PICSTORE_STORAGE_OPERATION_ERROR = 102;
}