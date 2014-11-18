<?php

/**
 * 缓存异常
 */
class Cache_Exception extends Component_Exception {

    /**
     * 环境不支持该类型的缓存
     */
    const E_CACHE_NOT_SUPPORT = 201;
}