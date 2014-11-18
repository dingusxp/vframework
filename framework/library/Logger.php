<?php

/**
 * 日志记录
 */
class Logger extends Component {
    
    // 日志级别： 调试
    const LEVEL_DEBUG = 1;
    
    // 日志级别： 信息
    const LEVEL_INFO = 2;
    
    // 日志级别： 注意
    const LEVEL_NOTICE = 4;
    
    // 日志级别： 警告
    const LEVEL_WARNING = 8;
    
    // 日志级别： 错误
    const LEVEL_ERROR = 16;
    
    // 日志级别： 批评
    const LEVEL_CRITICAL = 32;
    
    // 日志级别： 警告
    const LEVEL_ALERT = 64;
    
    // 日志级别：紧急
    const LEVEL_EMERGENCY = 128;
    
    // 日志参数
    private static $_logParam = array();
    
    // 日志ID
    private static $_logId = null;
    
    /**
     * 各级别日志名称
     * @var type 
     */
    private static $_levelNames = array(
        self::LEVEL_DEBUG => 'debug',
        self::LEVEL_INFO => 'info',
        self::LEVEL_NOTICE => 'notice',
        self::LEVEL_WARNING => 'warning',
        self::LEVEL_ERROR => 'error',
        self::LEVEL_CRITICAL => 'critical',
        self::LEVEL_ALERT => 'alert',
        self::LEVEL_EMERGENCY => 'emergency',
    );

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
    
    /**
     * 获取当前进程日志 ID
     */
    public static function getLogId() {
        
        if (!self::$_logId) {
            if (defined('LOG_ID')) {
                self::$_logId = LOG_ID;
            } else {
                self::$_logId = V::timestamp().mt_rand(100000000, 999999999);
            }
        }
        
        return self::$_logId;
    }

    /**
     * 设置 需要记录的日志变量
     * @param type $key
     * @param type $value
     */
    public static function setParam($key, $value = null) {
        
        // 支持参数数组，批量设置
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::setParam($k, $v);
            }
            return;
        }
        
        // 如果值为空，删除项
        if (null === $value) {
            unset(self::$_logParam[$key]);
            return;
        }

        self::$_logParam[$key] = $value;
    }
    
    /**
     * 返回全部设置的日志变量
     */
    public static function getParam($key = null) {

        return $key ? self::$_logParam[$key] : self::$_logParam;
    }
    
    /**
     * 获取 级别 对应的名称
     * @param type $level
     */
    public static function getTypeName($level) {
        
        return array_key_exists($level, self::$_levelNames)
                ? self::$_levelNames[$level]
                : 'unknown';
    }
}