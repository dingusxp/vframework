<?php
/**
 * 默认全局配置
 *
 * 请勿随便修改此配置文件 ！！！
 * 如果需要覆盖某配置，在应用下重写对应配置项即可
 */

return array(

    /**
     * 本地临时目录
     */
    'tmp_path' => APP_PATH . '/tmp',

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
     * 视图引擎
     */
    'view' => array(
        'engine' => 'PHP',
        'option' => array(
                        'template_dir' => V_PATH . '/tpl',
                        'template_ext' => 'php',
                    )
    ),
    
    /**
     * 图片处理
     */
    'image' => array(
        'engine' => 'GD',
        'option' => array(
                    ),
    ),
    
    /**
     * 加密解密
     */
    'cryptor' => array(
        'engine' => 'Xor',
        'option' => array(
            'keys' => array('vframework', 'cryptor'),
        ),
    ),
);