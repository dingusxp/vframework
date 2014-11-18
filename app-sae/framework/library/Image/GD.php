<?php

/**
 * 使用 GD 库进行图片处理
 * 注意：需要 GD2.0.1 以上支持！
 */
class Image_GD extends Image_Abstract {

    /**
     * 对图片类型的支持
     * @var <type>
     */
    private static $_supports = array();

    /**
     * 文本字体
     */
    private $_textFont = '';

    /**
     * 构造函数
     * @param array $option
     */
    public function __construct(array $option = array()) {

        if (!function_exists('gd_info')) {
            throw new Image_Exception(V::t('GD lib has not installed', array(), 'framework'),
                    Image_Exception::E_IMAGE_RUMTIME_ERROR);
        }

        // 字体
        $this->_textFont = dirname(__FILE__) . '/fonts/Duality.ttf';
        if (isset($option['text_font']) && FS::isFile($option['text_font'])) {
            $this->setTextFont($option['text_font']);
        }

        parent::__construct($option);
    }

    /**
     * 获取 im 对象
     */
    public function getIm() {
        if (null === $this->_im) {
            throw new Image_Exception(V::t('Image not init yet', array(), 'framework'),
                    Image_Exception::E_IMAGE_IM_NOT_INIT);
        }
        return $this->_im;
    }

    /**
     * 新建一个图片
     * @param <type> $width
     * @param <type> $height
     * @param <type> $bgColor
     */
    public function create($width, $height, $bgColor = '#FFFFFF', $type = Image::TYPE_JPG) {

        // 检查类型
        $this->_checkSupport($type);

        $this->_im = imagecreatetruecolor($width, $height);
        if (!$this->_im) {
            throw new Image_Exception(V::t('Image init failed', array(), 'framework'),
                    Image_Exception::E_IMAGE_INIT_ERROR);
        }

        $bgColor = Image::parseColor($bgColor);
        $color = imagecolorallocate($this->_im, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefilledrectangle($this->_im, 0, 0, $width, $height, $color);
        imagecolordeallocate($this->_im, $color);
        $this->_width = $width;
        $this->_height = $height;
        $this->_type = $type;
    }

    /**
     * 创建验证码
     * @param <type> $code
     * @param <type> $width
     * @param <type> $height
     */
    public function createCaptcha($code, $width = 200, $height = 60) {

        // 背景
        $this->_im = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($this->_im,
            (int)(0xFFFFFF % 0x1000000 / 0x10000),
            (int)(0xFFFFFF % 0x10000 / 0x100),
            0xFFFFFF % 0x100);
        imagefilledrectangle($this->_im, 0, 0, $width, $height, $bgColor);
        imagecolordeallocate($this->_im, $bgColor);

        // 干扰图像
        $bgLine = 5;
        $foreLine = 3;
        $dotNum = 100;
        $r = mt_rand(50, 200);
        $g = mt_rand(50, 200);
        $b = mt_rand(50, 200);
        $lineColor = imagecolorallocatealpha($this->_im, $r, $g, $b, 80);

        // 背景干扰线
        for ($i=0; $i < $bgLine; $i++) {
            imagesetthickness($this->_im, mt_rand(8, 16));
            imageline($this->_im, mt_rand(0, intval($width / 2)), mt_rand(0, $height), mt_rand(intval($width / 2), $width), mt_rand(0, $height), $lineColor);
        }

        // 横向干扰线
        for ($i=0; $i < $foreLine; $i++) {
            imagesetthickness($this->_im, mt_rand(4, 8));
            imageline($this->_im, 0, mt_rand(0, $height), $width, mt_rand(0, $height), $lineColor);
        }

        // 干扰点
        for ($i=0; $i < $dotNum; $i++) {
            $dotColor = imagecolorallocatealpha($this->_im, $r, $g, $b, mt_rand(0,20));
            imagesetpixel($this->_im, mt_rand(0, $width), mt_rand(0, $height), $dotColor);
        }

        // 文字颜色
        $foreColor = imagecolorallocate($this->_im,
            (int)(0x2040A0 % 0x1000000 / 0x10000),
            (int)(0x2040A0 % 0x10000 / 0x100),
            0x2040A0 % 0x100);

        // 根据验证码长度循环写入字符
        $hPadding = 10;
        $vPadding = 4;
        $span = 6;
        $length = strlen($code);
        $minWidth = intval(($width - 2 * $hPadding - ($length - 1) * $span) / $length);
        $minHeight = intval(($height - 2 * $vPadding) / 2);
        for($i = 0; $i < $length; ++$i) {
            $fontSize = max(20, min($minWidth, mt_rand($minHeight, 2 * $minHeight)));
            $angle = mt_rand(-12, 12);
            $letter = $code[$i];
            imagettftext($this->_im, $fontSize, $angle,
                $hPadding + $i * ($span + $minWidth),
                $vPadding + $fontSize + mt_rand(0, $height - $vPadding * 2 - $fontSize), $foreColor, $this->_textFont, $letter);
        }
        imagecolordeallocate($this->_im, $foreColor);

        $this->_width = $width;
        $this->_height = $height;
        $this->_type = Image::TYPE_GIF;
    }

    /**
     * 从文件载入图片
     * @param <type> $file
     */
    public function loadFromFile($file) {

        // 图片信息
        if (!$file || false === ($info = getimagesize($file))) {
            throw new Image_Exception(V::t('Image not exist or invalid: {name}', array('name' => $file), 'framework'),
                    Image_Exception::E_IMAGE_INIT_ERROR);
        }

        // 初始化 im
        $params = array(
            1 => array('type' => Image::TYPE_GIF, 'function' => 'imagecreatefromgif'),
            2 => array('type' => Image::TYPE_JPG, 'function' => 'imagecreatefromjpeg'),
            3 => array('type' => Image::TYPE_PNG, 'function' => 'imagecreatefrompng'),
        );
        if (!array_key_exists($info[2], $params)) {
            throw new Image_Exception(V::t('Image type not support:{type}', array('type' => $info[2]), 'framework'),
                    Image_Exception::E_IMAGE_INIT_ERROR);
        }

        $type = $params[$info[2]]['type'];
        $function = $params[$info[2]]['function'];
        $this->_checkSupport($type);
        $this->_im = $function($file);
        if (!$this->_im) {
            throw new Image_Exception(V::t('Image init failed', array(), 'framework'),
                    Image_Exception::E_IMAGE_INIT_ERROR);
        }

        $this->_width = $info[0];
        $this->_height = $info[1];
        $this->_type = $type;
    }

    /**
     * 调整尺寸
     * @param <type> $newWidth
     * @param <type> $newHeight
     * @param <type> $resizeMode
     */
    public function resize($newWidth, $newHeight, $resizeMode = Image::RESIZE_STRETCH) {

        // 基本信息
        $dx = $dy = $sx = $sy = 0;
        $dw = intval($newWidth);
        $dh =  intval($newHeight);
        $sw = $this->_width;
        $sh = $this->_height;
        if ($dw < 0 || $dh < 0) {
            throw new Image_Exception(V::t('New width or height invalid', array(), 'framework'),
                    Image_Exception::E_IMAGE_PARAM_ERROR);
        }

        // 修正宽高比例和位置
        switch ($resizeMode) {
            case Image::RESIZE_CENTER:

                // 指定大小裁剪（居中）
                if ($sw / $sh > $dw / $dh) {
                    $bh = $sh;
                    $bw = intval($bh * $dw / $dh);
                    $sx = intval(($sw - $bw) / 2);
                    $sw = $bw;
                } else {
                    $bw = $sw;
                    $bh = intval($bw * $dh / $dw);
                    $sy = intval(($sh - $bh) / 2);
                    $sh = $bh;
                }
                break;
            case Image::RESIZE_SMART:

                // 以最大边为准，保持比例
                if ($sw / $sh > $dw / $dh) {
                    $dh = intval($sh * $newWidth / $sw);
                } else {
                    $dw = intval($sw * $newHeight / $sh);
                }
                break;
            case Image::RESIZE_STRETCH:
            default:
                // 按指定尺寸拉伸
        }

        // 处理
        $img = new self();
        $img->create($dw, $dh);
        $destIm = $img->getIm();
        $srcIm = $this->getIm();
		imagecopyresampled($destIm, $srcIm, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
        imagedestroy($srcIm);
        $this->_im = $destIm;
        $this->_width = $dw;
        $this->_height = $dh;
    }

    /**
     * 设置字体
     */
    public function setTextFont($fontFile) {

        if ($fontFile && FS::isFile($fontFile)) {
            $this->_textFont = $fontFile;
        }
    }

    /**
     * 图片上写文本
     * @param <type> $text
     * @param <type> $pos
     * @param <type> $color
     * @param <type> $fontSize
     */
    public function addText($text, $px, $py, $color = '#000000', $fontSize = 5) {

        $rgb = Image::parseColor($color);
        $color = imagecolorallocate($this->getIm() , $rgb[0] , $rgb[1] , $rgb[2]);
        imagestring($this->getIm(), $fontSize , $px , $py , $text , $color);
    }

    /**
     * 将图片合并过来
     * @param Image_Abstract $im
     * @param <type> $px
     * @param <type> $py
     * @param <type> $w
     * @param <type> $h
     * @param <type> $opacity
     */
    public function merge(Image_Abstract $im, $px, $py, $w, $h, $opacity = 100) {

        if (! ($im instanceof Image_GD)) {
            throw new Image_Exception('被合并的图片必须是 Image_GD 对象', Image_Exception::E_IMAGE_PARAM_ERROR);
        }

        $sourceIm = $this->getIm();
        imagealphablending($sourceIm, true);
        if ($this->_type == Image::TYPE_PNG) {
            $temp = imagecreatetruecolor($this->_width, $this->_height);
            imagecopy($temp, $sourceIm, 0, 0, 0, 0, $this->_width, $this->_height);
            imagecopy($temp, $im->getIm(), $px, $py, 0, 0, $w, $h);
            $this->_im = $temp;
        } else {
            imagecopymerge($sourceIm, $im->getIm(), $px, $py, 0,0, $w, $h, $opacity);
        }
    }

    /**
     * 输出图片内容
     * @param <type> $target
     * @param <type> $option
     */
    public function output($target = Image::OUTPUT_WEB_RESPONSE, $option = array()) {

        // 参数
        // 输出图片类型
        if (empty($option['type'])) {
            if (!empty($option['file'])) {
                $option['type'] = Image::getTypeByExt(FS::getExt($option['file']));
            } else {
                $option['type'] = $this->_type;
            }
        }
        $this->_checkSupport($option['type']);

        // 图片质量： （JPG格式设置）
        if (!isset($option['quality'])) {
            if ($option['type'] == Image::TYPE_JPG) {
                $option['quality'] = 90;
            } else {
                $option['quality'] = null;
            }
        }

        $params = array(
            Image::TYPE_PNG => array('function' => 'imagepng', 'mime' => 'image/png'),
            Image::TYPE_JPG => array('function' => 'imagejpeg', 'mime' => 'image/jpeg'),
            Image::TYPE_GIF => array('function' => 'imagegif', 'mime' => 'image/gif'),
        );
        $function = $params[$option['type']]['function'];
        $mime = $params[$option['type']]['mime'];

        $im = $this->getIm();
        switch ($target) {
            case Image::OUTPUT_FILE:
                if (empty($option['file'])) {
                    throw new Image_Exception(V::t('No save file name given', array(), 'framework'),
                        Image_Exception::E_IMAGE_PARAM_ERROR);
                }
                $function($im, $option['file'], $option['quality']);
                break;
            case Image::OUTPUT_BROWSER:
                header('Content-type: ' . $mime);
                $function($im, null, $option['quality']);
                break;
            case Image::OUTPUT_WEB_RESPONSE:
            default:
                $response = Web_Response::getInstance();
                $response->setNoCache();
                $response->setHeader('content-type', $mime);
                $response->setOutputObContent(false);
                $response->setBody('');
                $response->output();
                $function($im, null, $option['quality']);
                break;
        }
    }

    /**
     * 是否支持指定格式的图片
     * @param <type> $type
     */
    public function isSupport($type) {

        // 缓存
        if (array_key_exists($type, self::$_supports)) {
            return self::$_supports[$type];
        }

        // 检查
        $bit = array(
            Image::TYPE_PNG => IMG_PNG,
            Image::TYPE_JPG => IMG_JPG,
            Image::TYPE_GIF => IMG_GIF,
        );
        self::$_supports[$type] = imagetypes() & $bit[$type] ? true : false;
        return self::$_supports[$type];
    }

    /**
     * 检查是否支持指定类型的图片
     * @param <type> $type
     */
    private function _checkSupport($type) {

        if (!$this->isSupport($type)) {
            throw new Image_Exception(V::t('Image type not support:{type}', array('type' => $type), 'framework'),
                Image_Exception::E_IMAGE_TYPE_NOT_SUPPORT);
        }
    }
}