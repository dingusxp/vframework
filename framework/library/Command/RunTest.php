<?php
/**
 * 执行测试用例类
 */

class Command_RunTest extends Command_Abstract {

    /**
     * 执行测试用例
     * @param type $params
     */
    public function execute($params) {

        // name
        if (empty($params['name'])) {
            echo 'Usage: ', PHP_EOL;
            echo 'php test.php -name <TestCaseName>', PHP_EOL;
            echo 'Sample: ', PHP_EOL;
            echo 'php test.php -name Hello', PHP_EOL;
            exit;
        }

        // dir
        $testcaseLib = dirname(Command::getEnv('SCRIPT_NAME')).DIRECTORY_SEPARATOR.'Test';
        if (!empty($params['dir']) && FS::isDir($params['dir'])) {
            $testcaseLib = $params['dir'];
        }

        try {
            $testName = $params['name'];
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
        } catch (Exception $e) {
            echo 'Exeption:', $e->getMessage(), PHP_EOL;
        }

        try {
            $obj = new $className();
            $reflection = new ReflectionClass($className);
            $methods = $reflection->getMethods();

            $totalNum = $passNum = $failNum = 0;
            echo $className, ':', PHP_EOL;
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
}