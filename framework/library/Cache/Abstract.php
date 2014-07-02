<?php

/**
 * 缓存抽象类
 */
abstract class Cache_Abstract {

    /**
     * 设置项
     * @var <type>
     */
    protected $_option ;

    public function  __construct($option = array()) {

        $this->_option = $option;
    }

    /**
     * 保存缓存资料
     *
     * @param  string  $id
     * @param  mixed   $value
     * @return boolean
     */
    abstract public function save($id, $data, $expired = null);

    /**
     * 加载缓存数据
     *
     * @param  string   $id
     * @return mixed|false
     */
    abstract public function load($id);

    /**
     * 移除缓存数据
     *
     * @param  string  $id
     * @return boolean
     */
    abstract public function remove($id);

    /**
     * 移除所有缓存数据
     *
     * @return boolean
     */
    abstract public function clean();
}