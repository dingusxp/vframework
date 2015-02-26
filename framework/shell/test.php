<?php
/**
 * 测试入口
 *
 * 运行方法：
 * test.php 测试名；
 * 如 php test.php -name Hello
 */

define('APP_PATH', dirname(__FILE__));
define('V_DEBUG', true);

define('COMMAND_NAME', 'RunTest');

try {
    require_once dirname(APP_PATH) . '/V.php';
    V::loadBootstrap('cli')->run();
} catch (Exception $e) {
    echo defined('V_DEBUG') && V_DEBUG ? $e->getMessage() : 'Error';
}