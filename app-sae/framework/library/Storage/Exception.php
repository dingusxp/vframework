<?php

/**
 * Storage 异常
 */
class Storage_Exception extends Component_Exception {
    
    /**
     * 初始化 storage 失败
     */
    const E_STORAGE_INIT_ERROR = 301;

    /**
     * 木有权限
     */
    const E_STORAGE_ACCESS_DENIED = 302;

    /**
     * 写入失败
     */
    const E_STORAGE_WRITE_ERROR = 303;

    /**
     * 读取失败
     */
    const E_STORAGE_READ_ERROR = 304;
}