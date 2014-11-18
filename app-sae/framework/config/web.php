<?php
/**
 * web 相关默认配置
 */

return array(

    /**
     * 路由相关配置
     */
    'router' => array(

        // 解析器
        'engine' => 'Simple',
        'option' => array(
            'action_key' => 'r',
            'action_seperater' => '/',
            'allow_controllers' => array(

            ),

            // 默认动作：当路由规则解析不出时出发
            // 可以留空，则解析不出时出发 404 错误
            'default_action' => array(
                    'operation' => 'action',
                    'param' => array(
                        'controller' => 'index',
                        'action' => 'index',
                        ),
            ),

            // 路由规则
            'rules' => array(
            ),
        ),

        // 404 错误的处理
        'on_error_404' => array(
            'operation' => 'view',
            'param' => array(
                'engine' => 'PHP',
                'option' => array(
                                'template_dir' => V_PATH . '/tpl',
                                'template_ext' => 'php',
                            ),
                'template' => 'error_404',
            ),
        ),

        // 500 错误的处理
        'on_error_500' => array(
            'operation' => 'view',
            'param' => array(
                'engine' => 'PHP',
                'option' => array(
                                'template_dir' => V_PATH . '/tpl',
                                'template_ext' => 'php',
                            ),
                'template' => 'error_500',
            ),
        ),
    ),

    // 视图
    'view' => array(
        'engine' => 'PHP',
        'option' => array(
            'template_dir' => APP_PATH . '/template',
            'template_ext' => 'php',
        ),
    ),

    // cookie
    'cookie' => array(
        'prefix' => '',
        'path' => '/',
        'domain' => '',
    ),

    // session
    'session' => array(
        // session handler
        // 如果使用，请使用 !!!静态类!!! 实现对应 open close read write destroy gc 函数
        'handler_class' => null,
    ),

    // request
    'request' => array(
        'check_injection' => false,
        'on_injection_detect' => null,
    ),

    // response
    'response' => array(
        'rewrite_callbacks' => array(),
        'enable_gzip' => false,
        'output_ob_content' => true,
    ),

);