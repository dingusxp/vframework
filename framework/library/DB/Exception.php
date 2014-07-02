<?php

/**
 * DB 操作异常
 */
class DB_Exception extends Component_Exception {

    /**
     * 未指定数据库
     */
    const E_DB_BAD_CONNECTION_PARAM  = 301;

    /**
     * 连接数据库失败
     */
    const E_DB_CONNECT_FAILED = 302;

    /**
     * 数据库查询失败
     */
    const E_DB_QUERY_FAILED = 303;
    
}