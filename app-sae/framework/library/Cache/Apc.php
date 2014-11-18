<?php

/**
 * APC 缓存
 */
class Cache_Apc extends Cache_Abstract {

    /**
     * 构造函数
     *
     * @param  array  $option
     * @throws Exception
     */
    public function __construct($option = array()) {
        
        if (!extension_loaded('apc')) {
            throw new Cache_Exception('APC extension is not ON', Cache_Exception::E_CACHE_NOT_SUPPORT);
        }

        $option['life_time'] = !empty($option['life_time']) ? intval($option['life_time']) : 0;
        
        parent::__construct($option);
    }

    /**
     * 保存缓存资料
     *
     * @param  mixed   $data
     * @param  string  $id
     * @return boolean
     */
    public function save($id, $data, $expired = null) {

        if (null === $expired) {
            $expired = $this->_option['life_time'];
        }

        return apc_store($id, $data, $expired);
    }

    /**
     * 加载缓存数据
     *
     * @param  string   $id
     * @return mixed|false
     */
    public function load($id) {
        
        return apc_fetch($id);
    }

    /**
     * 移除缓存数据
     *
     * @param  string  $id
     * @return boolean
     */
    public function remove($id) {
        
        return apc_delete($id);
    }

    /**
     * 移除所有缓存数据
     *
     * @return boolean
     */
    public function clean() {
        
        return apc_clear_cache('user');
    }
}