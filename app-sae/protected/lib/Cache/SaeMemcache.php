<?php

/**
 * memcache 缓存（SAE）
 */
class Cache_SaeMemcache extends Cache_Abstract {

    /**
     * 实例化的 Memcache 对像
     *
     * @var Memcache
     */
    protected $_memcache = null;

    /**
     * 构造函数
     *
     * @param  array  $option
     * @throws Exception
     */
    public function __construct($option = array()) {

        $option['life_time'] = !empty($option['life_time']) ? intval($option['life_time']) : 0;
        $option['compression'] = !empty($option['compression']) ? true : false;

        parent::__construct($option);
    }

	private function _getMemcache() {

		if (null === $this->_memcache) {
			$this->_memcache = memcache_init();
		}
		if (!$this->_memcache) {
			throw new Cache_Exception('Init memcache failed', Cache_Exception::E_CACHE_NOT_SUPPORT);
		}
		return $this->_memcache;
	}

    /**
     * 保存缓存资料
     *
     * @param  mixed   $data
     * @param  string  $id
     * @return boolean
     */
    public function save($id, $data, $expired = null) {

        $compression = $this->_option['compression'] ? MEMCACHE_COMPRESSED : 0;
        return memcache_set($this->_getMemcache(), $id, $data, $compression, $expired);
    }

    /**
     * 加载缓存数据
     *
     * @param  string   $id
     * @return mixed|false
     */
    public function load($id) {

        return memcache_get($this->_getMemcache(), $id);
    }

    /**
     * 移除缓存数据
     *
     * @param  string  $id
     * @return boolean
     */
    public function remove($id) {

        return memcache_delete($this->_getMemcache(), $id);
    }

    /**
     * 移除所有缓存数据
     *
     * @return boolean
     */
    public function clean() {

        memcache_flush($this->_getMemcache());
    }
}