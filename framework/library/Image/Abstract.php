<?php

/**
 * 图片处理抽象类
 */
abstract class Image_Abstract {

    /**
     * 图片对象
     * @var <type>
     */
    protected $_im = null;

    /**
     * 图片宽度
     * @var <type>
     */
    protected $_width = 0;

    /**
     * 图片高度
     * @var <type>
     */
    protected $_height = 0;

    /**
     * 图片类型
     * @var <type>
     */
    protected $_type = 0;

    /**
     * 构造函数
     */
    public function __construct(array $option = array()) {

        // 自动载入
        if (!empty($option['src'])) {
            $this->loadFromFile($option['src']);
        }
    }

    /**
     * 新建一个图片
     * @param <type> $width
     * @param <type> $height
     * @param <type> $bgColor
     */
    abstract public function create($width, $height, $bgColor = '#FFFFFF', $type = Image::TYPE_JPG);

    /**
     * 创建验证码
     * @param <type> $code
     * @param <type> $width
     * @param <type> $height
     */
    abstract public function createCaptcha($code, $width = 200, $height = 60);

    /**
     * 从文件载入图片
     * @param <type> $file
     */
    abstract public function loadFromFile($file);

    /**
     * 获取图片宽度
     */
    public function getWidth() {
        return $this->_width;
    }

    /**
     * 获取图片高度
     */
    public function getHeight() {
        return $this->_height;
    }

    /**
     * 获取图片类型
     * @return <type>
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * 调整尺寸
     * @param <type> $newWidth
     * @param <type> $newHeight
     * @param <type> $resizeMode
     */
    abstract public function resize($newWidth, $newHeight, $resizeMode = Image::RESIZE_STRETCH);

    /**
     * 图片上写文本
     * @param <type> $text
     * @param <type> $pos
     * @param <type> $color
     * @param <type> $fontSize
     */
    abstract public function addText($text, $px, $py, $color = '#FFFFFF', $fontSize = 5);

    /**
     * 将图片合并过来
     * @param Image_Abstract $im
     * @param <type> $px
     * @param <type> $py
     * @param <type> $w
     * @param <type> $h
     * @param <type> $opacity
     */
    abstract public function merge(Image_Abstract $im, $px, $py, $w, $h, $opacity = 100);

    /**
     * 输出图片内容
     * @param <type> $target
     * @param <type> $option
     */
    abstract public function output($target = Image::OUTPUT_WEB_RESPONSE, $option = array());

    /**
     * 是否支持指定格式的图片
     * @param <type> $type
     */
    abstract public function isSupport($type);
}