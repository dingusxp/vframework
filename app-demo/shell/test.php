<?php
/**
 * 测试入口
 *
 * 运行方法：
 * test.php 测试名；
 * 如 php test.php Hello
 */

define('APP_PATH', dirname(dirname(__FILE__)));
define('V_DEBUG', true);

try {
    require_once dirname(APP_PATH) . '/framework/V.php';
    V::loadBootstrap('runtime')->run();
} catch (Exception $e) {
    echo defined('V_DEBUG') && V_DEBUG ? $e->getMessage() : 'Error';
}

// argv
if (empty($argv[1])) {
    echo 'Usage: ', PHP_EOL;
    echo 'php test.php <TestCaseName>', PHP_EOL;
    echo 'Sample: ', PHP_EOL;
    echo 'php test.php Hello', PHP_EOL;
    exit;
}
$testcaseLib = dirname(__FILE__).'/Test';
if (!empty($argv[2]) && FS::isDir($argv[2])) {
    $testcaseLib = $argv[2];    
}
    
try {
    $testName = $argv[1];
    if (!preg_match('/^\w+$/', $testName)) {
        echo 'Error: Bad test name given: ', $testName, PHP_EOL;
        exit;
    }
    $file = FS::joinPath($testcaseLib, str_replace('_', '/', $testName).'.php');
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