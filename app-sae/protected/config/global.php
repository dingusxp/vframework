<?php
/**
 * 全局配置
 */

return array(
    
    /**
     * 本地临时目录
     */
    'tmp_path' => defined('SAE_TMP_PATH') ? SAE_TMP_PATH : APP_PATH.'/tmp',
    
    /**
     * 静态资源根地址
     */
    'static_url' => '/static',

    /**
     * 日志记录
     */
    'logger' => array(
        // 0: none, 1: debug, 2: info, 4: notice, 8: warning, 16: error, 
        // 32: critical, 64: alert, 128: emergency
        'log_level' => 0,
        'engine' => 'file',
        'option' => array(
            'path' => APP_PATH . '/log',
            'date_format' => 'Ymd',
            // 各级别日志记录到的文件名；可以不同级别写到同一个文件
            'log_filename' => array(
                1 => 'debug',
                2 => 'info',
                4 => 'notice',
                8 => 'warning',
                16 => 'error',
                32 => 'critical',
                64 => 'alert',
                128 => 'emergency',
            ),
        ),
    ),

    /**
     * 语言包
     */
    'language' => array(
        'path' => APP_PATH . '/language',
        'locale' => 'zh_CN',
    ),

    /**
     * 类库路径
     */
    'libraries' => array(),

    /**
     * 预加载类
     */
    'imports' => array(),

    /**
     * 类 自动加载 回调
     */
    'autoloads' => array(),
    
    /**
     * 预置钩子
     * 格式：
     * + array(handlerName, callback),
     */
    'hooks' => array(),

    /**
     * 时区
     */
    'timezone' => 'Asia/Shanghai',

    /**
     * 路由配置位置
     */
    'router_key' => 'web.router',
    
    /**
     * 加密解密
     */
    'cryptor' => array(
        'engine' => 'Xor',
        'option' => array(
            'keys' => array('vframework', 'cryptor', 'app-sae'),
        ),
    ),

    /**
     * 图片处理
     */
    'image' => array(
        'engine' => 'GD',
        'option' => array('text_font' => dirname(__FILE__) . '/fonts/CaviarDreams_Bold.ttf'),
    ),

    /**
     * 缓存
     */
    'cache' => array(
        'engine' => 'SaeMemcache',
        'option' => array(
        ),
    ),
    
    /**
     * 数据库连接
     */
    'db' => array(
        'engine' => 'saeMysql',
        'option' => array(
        ),
    ),
);