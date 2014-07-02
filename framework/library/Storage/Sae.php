<?php

/**
 * Storage SAE
 */
class Storage_Sae extends Storage_Abstract {

    /**
     * SAE Storage 对象
     * @var <type>
     */
    private $_storage = null;

    /**
     * domain
     * @var <type>
     */
    private $_domain = '';

    /**
     * 构造函数
     * @param array $option
     */
    public function  __construct(array $option = array()) {

        $option = $option +
                    array(
                        'domain' => 'data',
                    );

        $this->_domain = $option['domain'];
        $this->_storage = new SaeStorage();

        parent::__construct($option);
    }

    /**
     * 写文件
     * @param <type> $path
     * @param <type> $content
     */
    public function write($path, $content) {

        return $this->_storage->write($this->_domain, $path, $content);
    }

    /**
     * 上传本地文件到 storage
     * @param <type> $path
     * @param <type> $localPath
     */
    public function upload($path, $localPath) {

        return $this->_storage->upload($this->_domain, $path, $localPath);
    }

    /**
     * 读取文件内容
     */
    public function read($path) {

        return $this->_storage->read($this->_domain, $path);
    }

    /**
     * 删除文件
     * @param <type> $path
     */
    public function remove($path) {

        return $this->_storage->delete($this->_domain, $path);
    }

    /**
     * 删除文件夹
     * @param <type> $path
     */
    public function rmdir($path) {

        $list = $this->_storage->getListByPath($this->_domain, $path, 1000);
        if (!$list) {
            return true;
        }

        foreach ($list as $path) {
            $this->_storage->delete($this->_domain, $path);
        }

        return true;
    }

    /**
     * 文件是否存在
     * @param <type> $path
     */
    public function isExist($path) {

        return $this->_storage->fileExists($this->_domain, $path);
    }

    /**
     * 获取文件对应 url
     * @param <type> $path
     */
    public function getUrl($path) {

        return $this->_storage->getUrl($this->_domain, $path);
    }
}