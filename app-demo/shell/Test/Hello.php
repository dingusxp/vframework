<?php
/**
 * test hello
 */

class Test_Hello {

    /**
     * __construct
     */
    public function __construct() {
        
    }

    /**
     * 测试 say method
     */
    public function testSay() {

        $expected = 'Hello, world'.PHP_EOL;
//        $expected = 'Hello, world'; // will fail

        ob_start();
        Hello::say();
        $content = ob_get_clean();

        return $content == $expected;
    }
}