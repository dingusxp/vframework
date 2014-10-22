<?php
/**
 * 图片存储类
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

        // 杂项；原图留存
        'misc' => array(
            'type_limit' => array(Image::TYPE_GIF, Image::TYPE_JPG, Image::TYPE_PNG),
            'size_limit' => 2000000,
            'watermark' => array(
            ),
            'thumb' => array(),
        ),
   ),

 */

class PicStore {
    
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
     * 图片处理引擎
     * @var type 
     */
    private $_image = null;
    
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
                throw new PicStore_Exception('Stroage engine is not configured', PicStore_Exception::E_PARAM_ERROR);
            }
        }
        
        try {
            $this->_storage = Storage::factory($option['storage']);
        } catch (Exception $e) {
            throw new PicStore_Exception('Init storage failed: '.$e->getMessage(), PicStore_Exception::E_PICSTORE_LOAD_COMPONENT_FAILED);
        }
        
        // 图片处理配置
        if (empty($option['image'])) {
            $option['image'] = V::config('image');
            if (empty($option['image'])) {
                throw new PicStore_Exception('Image engine is not configured', PicStore_Exception::E_PARAM_ERROR);
            }
        }
        $this->_image = (array)$option['image'];
        
        $this->_cates = (array)$option['cate'];
    }

    /**
     * 获取指定 cate 的配置信息
     */
    private function _getCate($cate) {

        if (!array_key_exists($cate, $this->_cates)) {
            throw new PicStore_Exception('Cate not found: ' . $cate, PicStore_Exception::E_PARAM_ERROR);
        }

        return $this->_cates[$cate];
    }
    
    /**
     * 获取图像处理实例
     */
    private function _getImage() {
        
        try {
            return Image::factory($this->_image);
        } catch (Exception $e) {
            throw new PicStore_Exception('Init image engine failed: ', PicStore_Exception::E_PICSTORE_LOAD_COMPONENT_FAILED);
        }
    }

    /**
     * 生成一个唯一的 resourceId
     * 由 8位时间（精确到天） + 16 位 hash + 图片类型组成
     */
    private function _generateResourceId($imgPath, $imgType = null) {

        // 如果未指定图片类型，检测之
        if (!$imgType) {
            $imgType = Image::getTypeByExt(FS::getExt($imgPath));
        }
        $ext = Image::getExtByType($imgType);

        $maxTry = 5;
        while ($maxTry) {
            $hash = md5($imgPath . time() . mt_rand(100000, 999999));
            $resourceId = date('Ymd') . substr($hash, 8, 16) . $ext;
            if (!$this->_storage->isExist($this->_getPath($resourceId))) {
                return $resourceId;
            }
            $maxTry--;            
        }
        throw new PicStore_Exception('Generate resource id failed', PicStore_Exception::E_LOGIC_ERROR);
    }

    /**
     * 保存图片到指定 cate
     * @param <type> $imgPath
     * @param <type> $cate
     */
    public function store($imgPath, $cate = 'misc') {

        // 检查分类
        if (!$cate) {
            throw new PicStore_Exception('Cate does not specified', PicStore_Exception::E_PARAM_ERROR);
        }

        // 检查文件
        if (!FS::isFile($imgPath)) {
            throw new PicStore_Exception('Image does not exist: ' . $imgPath, PicStore_Exception::E_PARAM_ERROR);
        }

        // 检查图片类型
        $imgExt = FS::getExt($imgPath);
        $imgType = Image::getTypeByExt($imgExt);
        $option = $this->_getCate($cate);
        if (!empty($option['type_limit'])) {
            if (!$imgType || !in_array($imgType, $option['type_limit'])) {
                throw new PicStore_Exception('Invalid image type for cate: ' . $cate, PicStore_Exception::E_PARAM_ERROR);
            }
        }

        // 尺寸
        if (!empty($option['size_limit']) && filesize($imgPath) > $option['size_limit']) {
            throw new PicStore_Exception('Pic is out of size limit for cate ' . $cate . ':' . $imgPath, PicStore_Exception::E_PARAM_ERROR);
        }

        // 建立临时操作目录，进行水印，缩略图等操作
        $tmpDir = FS::tmpDir('picstore');
        try {

            // 拷贝图片到临时操作目录；如果指定要转换格式的话，转换格式先
            if (!empty($option['convert_type']) && $option['convert_type'] != $imgType) {
                $imgType = $option['convert_type'];
                $imgExt = Image::getExtByType($imgType);
                $resourceId = $this->_generateResourceId($imgPath, $imgType);
                $tmpPath = $tmpDir . '/' . $resourceId . '.' . $imgExt;
                $image = $this->_getImage();
                $image->loadFromFile($imgPath);
                $image->output(Image::OUTPUT_FILE, array('file' => $tmpPath, 'type' => $imgType));
                unset($image);
                FS::remove($imgPath);
            } else {
                $resourceId = $this->_generateResourceId($imgPath, $imgType);
                $tmpPath = $tmpDir . '/' . $resourceId . '.' . $imgExt;
                FS::move($imgPath, $tmpPath);
            }

            // 保留原图
            $oringalPath = $tmpPath.'.orginal.'.$imgExt;
            FS::copy($tmpPath, $oringalPath);
            $this->_storage->upload($this->_getPath($resourceId), $oringalPath);

            // 生成水印
            if (!empty($option['watermark'])) {

                // 生成水印
                $image = self::_getImage();
                $image->loadFromFile($tmpPath);
                $pos = Image::parsePosition($image, $option['watermark']['pos'], isset($option['watermark']['option']) ? $option['watermark']['option'] : array());
                if ($option['watermark']['type'] == 'image') {

                    // 图片水印
                    $tmpImage = self::_getImage();
                    $tmpImage->loadFromFile($option['watermark']['image']);
                    $image->merge($tmpImage, $pos['px'], $pos['py'], $tmpImage->getWidth(), $tmpImage->getHeight(),
                            $option['watermark']['opacity'] ? $option['watermark']['opacity'] : 80);
                    unset($tmpImage);
                } else {

                    // 文字水印
                    $image->addText($option['watermark']['text'], $pos['px'], $pos['py'],
                            isset($option['watermark']['color']) ? $option['watermark']['color'] : '#000000',
                            isset($option['watermark']['font_size']) ? $option['watermark']['font_size'] : 5);
                }
                $image->output(Image::OUTPUT_FILE, array('file' => $tmpPath, 'type' => $imgType));
                unset($image);
                $watermarkPath = $tmpPath.'.watermark.'.$imgExt;
                FS::copy($tmpPath, $watermarkPath);
                $this->_storage->upload($this->_getPath($resourceId, 'watermark'), $watermarkPath);
            }

            // 生成缩略图
            if (!empty($option['thumb'])) {
                foreach($option['thumb'] as $thumb => $thumbOption) {
                    $image = self::_getImage();
                    $image->loadFromFile($tmpPath);
                    $image->resize($thumbOption['width'], $thumbOption['height'], $thumbOption['resize_mode']);
                    $thumbPath = $tmpPath.'.'.$thumb.'.'.$imgExt;
                    $image->output(Image::OUTPUT_FILE, array('file' => $thumbPath));
                    $this->_storage->upload($this->_getPath($resourceId, $thumb), $thumbPath);
                    unset($image);
                }
            }

            // 清空临时目录
            FS::rmdir($tmpDir);

            // 返回 resourceId
            return $resourceId;
        } catch (Exception $e) {
            FS::rmdir($tmpDir);
            throw new PicStore_Exception('picstore save failed:'.$e->getMessage(), PicStore_Exception::E_PASSON_EXCEPTION);
        }
    }

    /**
     * 获取图片资源的路径（读）
     * @param <type> $resourceId
     * @param <type> $thumb
     */
    private function _getPath($resourceId, $thumb = null) {

        // 检查合法性
        if (!$resourceId || strlen($resourceId) < 24) {
            throw new PicStore_Exception('Invalid resource id: ' . $resourceId, PicStore_Exception::E_PARAM_ERROR);
        }

        if (!$thumb) {
            $thumb = 'original';
        }

        return substr($resourceId, 0, 4) . '/' . substr($resourceId, 4, 4) . '/' 
                . substr($resourceId, 8, 2) . '/' . substr($resourceId, 10, 14). '/'
                . $resourceId . '_' . $thumb . '.' . substr($resourceId, 24);
    }

    /**
     * 删除一个资源
     * @param <type> $resourceId
     */
    public function remove($resourceId) {

        try {
            return $this->_storage->rmdir(dirname($this->_getPath($resourceId)));
        } catch (Exception $e) {
            throw new PicStore_Exception('Storage operation error: '.$e->getMessage(), PicStore_Exception::E_PICSTORE_STORAGE_OPERATION_ERROR);
        }
    }

    /**
     * 获取资源对应的可读路径
     * @param <type> $resourceId
     * @param <type> $thumb
     * @return <type>
     */
    public function read($resourceId, $thumb = null) {

        try {
            return $this->_storage->read($this->_getPath($resourceId, $thumb));
        } catch (Exception $e) {
            throw new PicStore_Exception('Storage operation error: '.$e->getMessage(), PicStore_Exception::E_PICSTORE_STORAGE_OPERATION_ERROR);
        }
    }

    /**
     * 获取图片资源的 web 链接
     * @param <type> $resourceId
     * @param <type> $thumb
     */
    public function getUrl($resourceId, $thumb = null) {

        try {
            return $this->_storage->getUrl($this->_getPath($resourceId, $thumb));
        } catch (Exception $e) {
            throw new PicStore_Exception('Storage operation error: '.$e->getMessage(), PicStore_Exception::E_PICSTORE_STORAGE_OPERATION_ERROR);
        }
    }
}