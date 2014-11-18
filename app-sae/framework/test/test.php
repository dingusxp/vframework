<?php
/**
 * 测试入口
 *
 * 运行方法：
 * test.php 测试名；
 * 如 php test.php Model_Article
 */

define('APP_PATH', dirname(__FILE__));
define('V_DEBUG', true);

try {
    require_once dirname(APP_PATH) . '/V.php';
    V::loadBootstrap('runtime')->run();

    $testName = $argv[1];
    if (!preg_match('/^\w+$/', $testName)) {
        echo 'Error: Bad test name given: ', $testName, PHP_EOL;
        exit;
    }
    $file = dirname(__FILE__).'/'.str_replace('_', '/', $testName).'.php';
    if (!FS::isFile($file)) {
        echo 'Error: Class file not exist: ', $file, PHP_EOL;
        exit;
    }

    echo 'test file: ', $file, PHP_EOL, PHP_EOL;
    require $file;
    $className = 'Test_'.$testName;
    if (!class_exists($className)) {
        echo 'Error: Test class not exist: ', $className, PHP_EOL;
        exit;
    }

    runTest($className);
} catch (Exception $e) {
    echo 'Exeption:', $e->getMessage(), PHP_EOL;
}

function runTest($class) {

    try {
        $obj = new $class();
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods();

        $totalNum = $passNum = $failNum = 0;
        echo $class, ':', PHP_EOL;
        foreach ($methods as $method) {
            if (substr($method->name, 0, 4) == 'test') {
                echo '[::', $method->name, '] ';
                $result = call_user_func_array(array($obj, $method->name), array());
                $totalNum++;
                if ($result) {
                    echo ' (pass)', PHP_EOL;
                    $passNum++;
                } else {
                    echo ' (fail)', PHP_EOL;
                    $failNum++;
                }
            }
        }
        echo PHP_EOL, '============================', PHP_EOL;
        echo 'Total:', $totalNum, PHP_EOL;
        echo 'Pass:', $passNum, PHP_EOL;
        echo 'Fail:', $failNum, PHP_EOL;
    } catch (Exception $e) {
        echo 'Exeption:', $e->getMessage(), PHP_EOL;
    }
}