<?php

/**
 * memcache 缓存
 */
class Cache_Memcache extends Cache_Abstract {

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
        
        if (!class_exists('Memcache')) {
            throw new Cache_Exception('Memcache extension is not ON', Cache_Exception::E_CACHE_NOT_SUPPORT);
        }

        // 参数
        $option['life_time'] = !empty($option['life_time']) ? intval($option['life_time']) : 0;
        $option['enable_compress'] = !empty($option['enable_compress']) ? true : false;
        if (empty($option['servers']) || !is_array($option['servers'])) {
            $option['servers'] = array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'persistent' => true,
                )
            );
        }

        parent::__construct($option);
    }

    /**
     * 获取一个连接
     */
    private function _getMemcache() {

        if (null === $this->_memcache) {
            try {
                $this->_memcache = new Memcache();
                foreach ($this->_option['servers'] as $server) {
                    $this->_memcache->addServer($server['host'], $server['port'], $server['persistent']);
                }
            } catch (Exception $e) {
                throw new Cache_Exception($e);
            }
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
        
        $compression = $this->_option['enable_compress'] ? MEMCACHE_COMPRESSED : 0;
        return $this->_getMemcache()->set($id, $data, $compression, $expired);
    }

    /**
     * 加载缓存数据
     *
     * @param  string   $id
     * @return mixed|false
     */
    public function load($id) {
        
        return $this->_getMemcache()->get($id);
    }

    /**
     * 移除缓存数据
     *
     * @param  string  $id
     * @return boolean
     */
    public function remove($id) {
        
        return $this->_getMemcache()->delete($id);
    }

    /**
     * 移除所有缓存数据
     *
     * @return boolean
     */
    public function clean() {
        
        return $this->_getMemcache()->flush();
    }
}