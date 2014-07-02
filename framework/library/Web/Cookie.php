<?php

/**
 * Cookie 操作
 */
class Web_Cookie {

    /**
     * cookie 数据
     * @var array
     */
    private $_data = array();

    /**
     * 配置项
     * @var array
     */
    private $_option;

    /**
     * 实例
     * @var Web_Request
     */
    private static $_instance;

    /**
     * 构造函数： 解析传入参数
     */
    private function  __construct() {

        $this->_option = V::config('web.cookie');

		$prefixLength = 0;
        if (!empty($this->_option['prefix'])) {
            $prefixLength = strlen($this->_option['prefix']);
        }

        foreach ($_COOKIE as $key => $value) {
            if ($prefixLength) {
                if (0 === strpos($key, $this->_option['prefix'])) {
                    $key = substr($key, $prefixLength);
                } else {
                    break;
                }
            }
            $this->_data[$key] = $value;
        }
    }

    /**
     * 获取实例
     * @return <type>
     */
    public static function getInstance() {

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * getx
     * 获取 cookie 数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getx() {

        $keys = func_get_args();
        if ($keys) {
            return Arr::fetch($this->_data, $keys, '');
        }
        return $this->_data;
    }

    /**
     * get
     * 获取指定 cookie
     *
     * @param <type> $name
     * @param <type> $default
     * @return <type>
     */
    public function get($name, $default = '') {

        return isset($this->_data[$name]) ? $this->_data[$name] : $default;
    }

    /**
     * 设置 cookie
     *
     * @param <type> $name
     * @param <type> $value
     * @param <type> $expired
     * @param <type> $path
     * @param <type> $domain
     */
    public function set($name, $value, $expired = 0, $path = null, $domain = null) {

        $this->_data[$name] = $value;

        // 设置 COOKIE
        $expired = (0 == $expired) ? 0 : time() + intval($expired);
        $path = null !== $path ? $path : $this->_option['path'];
        $domain = null !== $domain ? $domain : $this->_option['domain'];
        setcookie($name, $value, $expired, $path, $domain);
    }

    /**
     * 生成写 cookie 的 http header 头
     * @param <type> $name
     * @param <type> $value
     * @param <type> $expired
     * @param <type> $path
     * @param <type> $domain
     * @return <type>
     */
    public function generateHeaderContent($name, $value, $expired = 0, $path = null, $domain = null) {

        return rawurlencode($name) . '=' . rawurlencode($value)
            . '; expires=' . gmdate('D, d-M-Y H:i:s \G\M\T', time() + intval($expired))
            . '; Path=' . (null !== $path ? $path : $this->_option['path'])
            . '; domain=' . (null !== $domain ? $domain : $this->_option['domain']);
    }
}