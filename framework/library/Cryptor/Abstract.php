<?php

/**
 * 加密抽象类
 */
abstract class Cryptor_Abstract {

    /**
     * 配置信息
     * @var <type>
     */
    protected $_option ;

    /**
     * 构造函数
     * @param <type> $option
     */
    public function  __construct(array $option = array()) {

        $this->_option = $option;
    }

    /**
     * 加密
     * @param <type> $string
     * @param <type> $skey
     */
    abstract public function encrypt($string, $skey = '');

    /**
     * 解密
     * @param <type> $string
     * @param <type> $skey
     */
    abstract public function decrypt($string, $skey = '');
}