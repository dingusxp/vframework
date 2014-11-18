<?php

/**
 * Storage 抽象类
 */
abstract class Storage_Abstract {

    /**
     * 配置信息
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
     * 写文件
     * @param <type> $path
     * @param <type> $content 
     */
    abstract public function write($path, $content);

    /**
     * 上传本地文件到 storage
     * @param <type> $path
     * @param <type> $localPath
     */
    abstract public function upload($path, $localPath);

    /**
     * 读取文件内容
     */
    abstract public function read($path);

    /**
     * 删除文件
     * @param <type> $path
     */
    abstract public function remove($path);

    /**
     * 删除文件夹
     * @param <type> $path
     */
    abstract public function rmdir($path);

    /**
     * 文件是否存在
     * @param <type> $path
     */
    abstract public function isExist($path);

    /**
     * 获取文件对应 url
     * @param <type> $path
     */
    abstract public function getUrl($path);
}