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
                'index',
            ),

            // 默认动作：当路由规则解析不出时触发；通常为首页
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
            'template_dir' => APP_PATH . '/tpl',
            'template_ext' => 'php',
        ),
    ),

    // cookie
    'cookie' => array(
        'prefix' => '',
        'path' => '/',
        'domain' => '',
    ),

    // request
    'request' => array(
        'check_injection' => false,
//        'on_injection_detect' => null,
    ),

    // response
    'response' => array(
        'rewrite_callbacks' => array(),
        'enable_gzip' => false,
        'output_ob_content' => true,
    ),

);