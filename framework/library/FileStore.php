<?php
/**
 * 文件存储类
 *
 * 配置示例

    // 存储引擎
    'storage' => array(
        'engine' => 'local',
        'option' => array(
            'base_path' => APP_PATH . '/www/asset/filestore',
            'base_url' => V::config('site_root') . '/asset/filestore',
        ),
    ),

    // cate
    'cate' => array(

        // 杂项；原文件留存
        'misc' => array(
            'type_limit' => array(),
            'size_limit' => 20000000,
        ),
   ),

 */

class FileStore {
    
    /**
     * 实例
     * @var type 
     */
    private static $_instances = array();

    /**
     * Storage 对象
     * @var <type>
     */
    private $_storage = null;
    
    /**
     * 存储目录配置
     * @var type 
     */
    private $_cates = array();
    
    /**
     * 根据配置获取获取实例
     * @param type $option
     */
    public static function getInstance($configKey) {
        
        if (empty(self::$_instances[$configKey])) {
            $option = V::config($configKey);
            self::$_instances[$configKey] = new self($option);
        }
        return self::$_instances[$configKey];        
    }
    
    /**
     * 构造函数
     * @param type $option
     */
    public function __construct($option) {
        
        // 存储器配置
        if (empty($option['storage'])) {
            $option['storage'] = V::config('storage');
            if (empty($option['storage'])) {
                throw new FileStore_Exception('Stroage engine is not configured', FileStore_Exception::E_PARAM_ERROR);
            }
        }
        
        try {
            $this->_storage = Storage::factory($option['storage']);
        } catch (Exception $e) {
            throw new FileStore_Exception('Init storage failed: '.$e->getMessage(), FileStore_Exception::E_FILESTORE_LOAD_COMPONENT_FAILED);
        }
        
        $this->_cates = (array)$option['cate'];
    }

    /**
     * 获取指定 cate 的配置信息
     */
    private function _getCate($cate) {

        if (!array_key_exists($cate, $this->_cates)) {
            throw new FileStore_Exception('Cate not found: ' . $cate, FileStore_Exception::E_PARAM_ERROR);
        }

        return $this->_cates[$cate];
    }

    /**
     * 生成一个唯一的 resourceId
     * 由 8位时间（精确到天） + 16 位 hash + 文件类型组成
     */
    private function _generateResourceId($filePath, $fileExt = null) {

        // 如果未指定文件类型，检测之
        if (!$fileExt) {
            $fileExt = FS::getExt($filePath);
        }

        $maxTry = 5;
        while ($maxTry) {
            $hash = md5($filePath . time() . mt_rand(100000, 999999));
            $resourceId = date('Ymd') . substr($hash, 8, 16) . $fileExt;
            if (!$this->_storage->isExist($this->_getPath($resourceId))) {
                return $resourceId;
            }
            $maxTry--;            
        }
        throw new FileStore_Exception('Generate resource id failed', FileStore_Exception::E_LOGIC_ERROR);
    }

    /**
     * 保存文件到指定 cate
     * @param <type> $filePath
     * @param <type> $cate
     */
    public function store($filePath, $cate = 'misc') {

        // 检查分类
        if (!$cate) {
            throw new FileStore_Exception('Cate does not specified', FileStore_Exception::E_PARAM_ERROR);
        }

        // 检查文件
        if (!FS::isFile($filePath)) {
            throw new FileStore_Exception('File does not exist: ' . $filePath, FileStore_Exception::E_PARAM_ERROR);
        }

        // 检查文件类型
        $fileExt = FS::getExt($filePath);
        $option = $this->_getCate($cate);
        if (!empty($option['type_limit'])) {
            if (!$fileExt || !in_array($fileExt, $option['type_limit'])) {
                throw new FileStore_Exception('Invalid file type for cate: ' . $cate, FileStore_Exception::E_PARAM_ERROR);
            }
        }

        // 尺寸
        if (!empty($option['size_limit']) && filesize($filePath) > $option['size_limit']) {
            throw new FileStore_Exception('File is out of size limit for cate ' . $cate . ':' . $filePath, FileStore_Exception::E_PARAM_ERROR);
        }

        // 存储
        $resourceId = $this->_generateResourceId($filePath, $fileExt);
        $path = $this->_getPath($resourceId);
            
        try {
            $this->_storage->upload($path, $filePath);

            // 返回 resourceId
            return $resourceId;
        } catch (Exception $e) {
            throw new FileStore_Exception('Storage operation error: '.$e->getMessage(), FileStore_Exception::E_FILESTORE_STORAGE_OPERATION_ERROR);
        }
    }

    /**
     * 获取地址
     * @param type $resourceId
     * @return type
     * @throws FileStore_Exception
     */
    private function _getPath($resourceId) {

        // 检查合法性
        if (!$resourceId || strlen($resourceId) < 24) {
            throw new FileStore_Exception('Invalid resource id: ' . $resourceId, FileStore_Exception::E_PARAM_ERROR);
        }

        return substr($resourceId, 0, 4) . '/' . substr($resourceId, 4, 4) . '/'
                . substr($resourceId, 8, 2) . '/' . substr($resourceId, 10, 14). '/'
                . $resourceId . '.' . substr($resourceId, 24);
    }

    /**
     * 删除一个资源
     * @param <type> $resourceId
     */
    public function remove($resourceId) {

        try {
            return $this->_storage->remove($this->_getPath($resourceId));
        } catch (Exception $e) {
            throw new FileStore_Exception('Storage operation error: '.$e->getMessage(), FileStore_Exception::E_FILESTORE_STORAGE_OPERATION_ERROR);
        }
    }

    /**
     * 获取资源对应的可读路径
     * @param <type> $resourceId
     * @param <type> $thumb
     * @param <type> $checkExist
     * @return <type>
     */
    public function read($resourceId) {

        try {
            return $this->_storage->read($this->_getPath($resourceId));
        } catch (Exception $e) {
            throw new FileStore_Exception('Storage operation error: '.$e->getMessage(), FileStore_Exception::E_FILESTORE_STORAGE_OPERATION_ERROR);
        }
    }

    /**
     * 获取资源对应的可访问 URL
     * @param <type> $resourceId
     * @param <type> $thumb
     * @param <type> $checkExist
     */
    public function getUrl($resourceId) {

        try {
            return $this->_storage->getUrl($this->_getPath($resourceId));
        } catch (Exception $e) {
            throw new FileStore_Exception('Storage operation error: '.$e->getMessage(), FileStore_Exception::E_FILESTORE_STORAGE_OPERATION_ERROR);
        }
    }
}