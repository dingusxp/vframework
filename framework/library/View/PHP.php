<?php
/**
 * 使用 php 作为模板语言
 */

class View_PHP extends View_Abstract {

    /**
     * 构造函数
     * @param <type> $option
     */
    public function  __construct($option = array()) {

        parent::__construct($option);
    }

    /**
     * 获取编译后的模板
     * @param <type> $tpl
     * @return <type>
     */
    protected function _getCompiledFile($tpl) {

        $ext = isset($this->_option['template_ext']) ? $this->_option['template_ext'] : 'php';
        return FS::joinPath($this->_option['template_dir'], $tpl . '.' . $ext);
    }
}

