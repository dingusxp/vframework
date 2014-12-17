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

        echo 'runtime:', V::runtime(), 'ms';
        return true;
    }

    /**
     * 测试语言包
     */
    public function testLang() {
    
        echo 'lang: ', V::t('this is a "{value}"', array('value' => 'test'));
        return true;
    }
}