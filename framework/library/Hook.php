<?php

/**
 * 全局 钩子 注册、触发 管理
 */
class Hook {
    
    /**
     * 注册的回调函数
     * @var type 
     */
    private static $_handlers = array();

    /**
     * 注册一个回调到指定事件
     * @param type $name
     * @param type $handler
     */
    public static function register($name, $handler) {
        
        if (!isset(self::$_handlers[$name])) {
            self::$_handlers[$name] = array();
        }
        if (!in_array($handler, self::$_handlers[$name])) {
            array_unshift(self::$_handlers[$name], $handler);
        }
    }
    
    /**
     * 移除指定事件的回调
     * @param type $name
     * @param type $handler
     */
    public static function unregister($name, $handler = null) {
        
        if (!$handler) {
            unset(self::$_handlers[$name]);
            return;
        }

        foreach (self::$_handlers[$name] as $k => $callback) {
            if ($callback[0] == $handler) {
                unset(self::$_handlers[$k]);
            }
        }
    }
    
    /**
     * 触发事件
     * @param type $name
     * @param type $params
     */
    public static function invoke($name, $params = null) {
        
        if (!isset(self::$_handlers[$name])) {
            return;
        }

        foreach (self::$_handlers[$name] as $callback) {
            $ret = call_user_func($callback, $params);
            if (false === $ret) {
                break;
            }
        }
    }
}