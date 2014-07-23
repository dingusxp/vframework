<?php
/**
 * web 相关默认配置
 */

return array(


    'router' => array(

        // 解析器
        'engine' => 'Regexp',
        'option' => array(

            // Regexp 路由规则
            'rules' => array(

                // index
				array(
					'pattern' => '/^[\/]?(\?|\#|$)/',
					'operation' => 'action',
					'param' => array(
						'controller' => 'Index',
						'action' => 'index',
					),
				),
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
    /*  smarty，需要 memcache 支持
    'view' => array(
        'engine' => 'SaeSmarty',
        'option' => array(
            'template_dir' => APP_PATH . '/tpl',
            'template_ext' => 'tpl',
            'compile_dir' => 'saemc://smartytpl/',
            'cache_dir' => 'saemc://smartytpl/',
            'compile_locking' => false,
        ),
    ),
    */
	/* 原生php模板
	*/
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
        'domain' => $_SERVER['HTTP_HOST'],
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
        'output_ob_content' => false,
    ),
);