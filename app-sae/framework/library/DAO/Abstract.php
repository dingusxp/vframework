<?php

/**
 * DAO 基类
 */
abstract class DAO_Abstract {

    /**
     * 配置参数
     * @var <type>
     */
    protected $_config = null;

    /**
     * 构造函数
     */
    public function  __construct(array $option = array()) {

        // 如果传入了配置，以传入配置优先
        if ($option) {
            $this->_config = $option + $this->_config();
        }
    }

    /**
     * 获取默认配置项
     * @param <type> $key
     * @param <type> $default
     * @return <type>
     */
    protected function _config($key = '', $default = null) {

        // 初始化配置
        if (null === $this->_config) {

            // remove DAO_
            $myName = substr(get_class($this), 4);
            $myName = preg_replace('/[A-Z]/', '_$0', $myName);
            $myName = trim($myName, '_');
            $myName = 'dao.' . strtolower($myName);
            $commonConfig = V::config('dao', array());
            $myConfig = V::config($myName, array());
            $this->_config = $myConfig + $commonConfig;
        }

        return $key ? (isset($this->_config[$key]) ? $this->_config[$key] : $default) : $this->_config;
    }
}