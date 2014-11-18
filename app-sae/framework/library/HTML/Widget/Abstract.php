<?php

/**
 * widget 抽象类
 */
abstract class HTML_Widget_Abstract {

    /**
     * 配置
     * @var <type>
     */
    protected $_option = array();

    /**
     * 构造函数
     * @param array $option
     */
    public function  __construct(array $option = array()) {

        $this->_option = $option;
    }

    /**
     * 生成 HTML
     */
    abstract public function getHTML();
}