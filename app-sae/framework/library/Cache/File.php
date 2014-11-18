<?php

/**
 * 文件类缓存
 */
class Cache_File extends Cache_Abstract {

    /**
     * 构造函数
     *
     * @param  array  $option
     * @throws Exception
     */
    public function __construct($option = array()) {

        // 默认缓存时间
        $option['life_time'] = !empty($option['life_time']) ? intval($option['life_time']) : 0;

        // 缓存目录
        if (!isset($option['cache_dir'])) {
            $option['cache_dir'] = APP_PATH . '/tmp/cache';
        }
        $option['cache_dir'] = rtrim($option['cache_dir'], '\\/');

        // 目录层级
        $option['cache_dir_level'] = !empty($option['cache_dir_level'])
                    ? min(max(1, intval($option['cache_dir_level'])), 4)
                    : 0;

        parent::__construct($option);
    }

    /**
     * 保存缓存资料
     *
     * @param  mixed   $value
     * @param  string  $id
     * @return boolean
     * @throws Exception
     */
    public function save($id, $data, $expired = null) {

        if (null === $expired) {
            $expired = $this->_option['life_time'];
        }

        $saveData = array(
            'id' => $id,
            'expired' => time() + ($expired ? $expired : 86400000),
            'data' => $data
        );
        $cacheFile = $this->_getFileName($id);
        return FS::write($cacheFile, $this->_encodeData($saveData));
    }

    /**
     * 加载缓存数据
     *
     * @param  string   $id
     * @return mixed|false
     */
    public function load($id) {

        $cacheFile = $this->_getFileName($id);
        if (FS::exist($cacheFile) && ($data = FS::read($cacheFile))) {
            $data = $this->_decodeData($data);
            if (!is_array($data) || $data['expired'] < time() || $data['id'] != $id) {
                unlink($cacheFile);
                return false;
            }
            return $data['data'];
        }
        return false;
    }

    /**
     * 将任意数据编码为字符串
     * @param <type> $data
     */
    private function _encodeData($data) {

        return serialize($data);
    }

    /**
     * 将编码字符串解码
     * @param <type> $data
     * @return <type>
     */
    private function _decodeData($data) {

        return unserialize($data);
    }

    /**
     * 移除缓存数据
     *
     * @param  string  $id
     * @return boolean
     */
    public function remove($id) {

        return FS::remove($this->_getFileName($id));
    }

    /**
     * 移除所有缓存数据
     *
     * @todo 遍历缓存目录下所有文件并删除
     * @return boolean
     */
    public function clean() {

        FS::cleandir($this->_option['cache_dir']);
        return true;
    }

    /**
     * 获取缓存文件名
     *
     * @param  string  $id
     * @return string
     */
    protected function _getFileName($id) {

        $path = $this->_option['cache_dir'];
        $name = substr(md5($id), 8, 8);
        $hash = Str::hash($name);

        $levels = array(1009, 1013, 1019, 1031);
        for ($i = 0; $i < $this->_option['cache_dir_level']; $i++) {
            $path = FS::joinPath($path, $hash % $levels[$i]);
        }

        return FS::joinPath($path, $name . '.cache');
    }
}