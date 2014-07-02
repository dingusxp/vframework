<?php

/**
 * 本地存储
 */
class Storage_Local extends Storage_Abstract {

    /**
     * 目录
     * @var <type>
     */
    private $_basePath = '';

    /**
     * 根链接
     * @var <type>
     */
    private $_baseUrl = '';

    /**
     * 构造函数
     * @param array $option
     */
    public function  __construct(array $option = array()) {

        $option = $option +
                    array(
                        'base_path' => APP_PATH . '/data',
                        'base_url' => '/data',
                    );

        // 根目录
        if (!FS::isDir($option['base_path'])) {
            throw new Storage_Exception(V::t('Directory does not exist: {msg}', array('msg' => $option['base_path']), 'framework'),
                    Storage_Exception::E_STORAGE_INIT_ERROR);
        }
        $this->_basePath = $option['base_path'];
        $this->_baseUrl = $option['base_url'];

        parent::__construct($option);
    }

    /**
     * 获取文件真实地址
     * @param <type> $path
     */
    private function _getPath($path) {

        $path = str_replace(array('../', './'), '', ltrim($path, '\\/'));
        return FS::joinPath($this->_basePath, $path);
    }

    /**
     * 获取文件 URL
     * @param <type> $path
     */
    private function _geUrl($path) {

        $path = str_replace(array('../', './'), '', ltrim($path, '\\/'));
        return $this->_baseUrl . '/' . $path;
    }

    /**
     * 写文件
     * @param <type> $path
     * @param <type> $content
     */
    public function write($path, $content) {

        return FS::write($this->_getPath($path), $content);
    }

    /**
     * 上传本地文件到 storage
     * @param <type> $path
     * @param <type> $localPath
     */
    public function upload($path, $localPath) {

        if (!FS::isFile($localPath)) {
            return false;
        }
        return FS::move($localPath, $this->_getPath($path));
    }

    /**
     * 读取文件内容
     */
    public function read($path) {

        return FS::read($this->_getPath($path));
    }

    /**
     * 删除文件
     * @param <type> $path
     */
    public function remove($path) {

        return FS::remove($this->_getPath($path));
    }

    /**
     * 删除文件夹
     * @param <type> $path
     */
    public function rmdir($path) {

        return FS::rmdir($this->_getPath($path));
    }

    /**
     * 文件是否存在
     * @param <type> $path
     */
    public function isExist($path) {

        return FS::isFile($this->_getPath($path));
    }

    /**
     * 获取文件对应 url
     * @param <type> $path
     */
    public function getUrl($path) {

        return $this->_geUrl($path);
    }
}