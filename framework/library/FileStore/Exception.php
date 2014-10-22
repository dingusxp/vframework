<?php

/**
 * filestore 异常类
 */
class FileStore_Exception extends V_Exception {

    /**
     * 初始化组件失败
     */
    const E_FILESTORE_LOAD_COMPONENT_FAILED = 101;

    /**
     * storage 操作失败
     */
    const E_FILESTORE_STORAGE_OPERATION_ERROR = 102;
}
