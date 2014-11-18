<?php
/**
 * Test class V
 */

class Test_V {

    /**
     * 输出时间戳
     * @return boolean
     */
    public function testTimestamp() {

        echo 'timestamp:', V::timestamp();
        return true;
    }

    /**
     * 运行时间
     * @return boolean
     */
    public function testRuntime() {

        usleep(mt_rand(1000, 10000));
        echo 'runtime:', V::runtime(), 'ms';
        return true;
    }
    
    /**
     * 测试 loader
     */
    public function testLoader() {
        
        try {
            V::loadBootstrap('runtime')->run();
        } catch (Exception $e) {
            echo PHP_EOL, 'Exception: ', $e->getMessage(), PHP_EOL;
        }
        return true;
    }
}