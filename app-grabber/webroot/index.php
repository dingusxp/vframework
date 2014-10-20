<?php
/**
 * 单点入口
 */

define('APP_PATH', dirname(dirname(__FILE__)));
define('V_DEBUG', true);

try {
    require_once dirname(APP_PATH) . '/framework/V.php';
    V::loadBootstrap('web', 'main')->run();
} catch (Exception $e) {
    echo defined('V_DEBUG') && V_DEBUG ? $e->getMessage() : 'Error';
}