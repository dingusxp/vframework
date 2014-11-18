<?php

/**
 * session
 */
class Web_Session {

    /**
     * 实例
     * @var <type>
     */
    private static $_instance = null;

    /**
     * 是否已初始化
     * @var <type>
     */
    private static $_inited = false;

    /**
     * 构造函数
     */
    private function  __construct() {

    }

    /**
     * 获取实例
     * @return <type>
     */
    public static function getInstance() {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * 初始化
     */
    private static function _init() {

        if (!self::$_inited) {
            self::$_inited = true;

            // register handler
            $config = V::config('web.session');
            if (!empty($config['handler_class']) && class_exists($config['handler_class'])) {
                $handler = $config['handler_class'];
                session_set_save_handler(
                    array( $handler ,  'open' ),
                    array( $handler ,  'close' ),
                    array( $handler ,  'read' ),
                    array( $handler ,  'write' ),
                    array( $handler ,  'destroy' ),
                    array( $handler ,  'gc' )
                    );
            }

            session_start();
        }
    }

    /**
     * 获取值
     * @param <type> $name
     * @return <type>
     */
    public function get($name) {

        self::_init();
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * 设置值
     * @param <type> $name
     * @param <type> $value
     */
    public function set($name, $value) {

        self::_init();
        $_SESSION[$name] = $value;
    }
}