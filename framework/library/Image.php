<?php

/**
 * 图片处理
 */
class Image extends Component {

    /**
     * 图片类型：GIF图片
     */
    const TYPE_GIF = 1;

    /**
     * 图片类型：JPG图片
     */
    const TYPE_JPG = 2;

    /**
     * 图片类型：PNG图片
     */
    const TYPE_PNG = 3;

    /**
     * 调整尺寸时若比例不对，拉伸以适应新尺寸
     */
    const RESIZE_STRETCH =  1;

    /**
     * 调整尺寸时若比例不对，居中裁剪
     */
    const RESIZE_CENTER = 2;

    /**
     * 调整尺寸时若比例不对，以最长边为准
     */
    const RESIZE_SMART = 3;

    /**
     * 图片通过 web_response 对象输出
     */
    const OUTPUT_WEB_RESPONSE = 1;

    /**
     * 图片写入文件
     */
    const OUTPUT_FILE = 2;

    /**
     * 图片输出到浏览器
     */
    const OUTPUT_BROWSER = 3;

    /**
     * 位置：九宫格位置
     */
    const POS_TOP_LEFT = 1;
    const POS_TOP_MIDDLE = 2;
    const POS_TOP_RIGHT = 3;
    const POS_MIDDLE_LEFT = 4;
    const POS_MIDDLE_MIDDLE = 5;
    const POS_MIDDLE_RIGHT = 6;
    const POS_BOTTOM_LEFT = 7;
    const POS_BOTTOM_MIDDLE = 8;
    const POS_BOTTOM_RIGHT = 9;

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }

    /**
     * 解析 #fff 表示的颜色为 RGB 方式
     * @param <type> $color
     */
    public static function parseColor($color) {

        if (is_array($color)) {
            return $color;
        }

        $color = ltrim($color, '#');
        if (!preg_match('/^([0-9a-f]{3}|[0-9a-f]{6})$/i', $color)) {
            return array(0, 0, 0);
        }
        if (strlen($color) == 3) {
            $r = hexdec(substr($color, 0, 1));
            $g = hexdec(substr($color, 1, 1));
            $b = hexdec(substr($color, 2, 1));
            $r = $r * $r;
            $g = $g * $g;
            $b = $b * $b;
        } else {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }
        return array($r, $g, $b);
    }

    /**
     * 解析指定图片位置的坐标
     * @param Image_Abstract $im
     * @param <type> $pos
     */
    public static function parsePosition(Image_Abstract $im, $pos, $option = array()) {

        $posX = $posY = 0;
        $minOffset = !empty($option['offset']) ? $option['offset'] : 0;
        $waterWidth = !empty($option['width']) ? $option['width'] : 0;
        $waterHeight = !empty($option['height']) ? $option['height'] : 0;
        $groundWidth = $im->getWidth();
        $groundHeight = $im->getHeight();

        switch($pos) {
            case 1 : //1为顶端居左
                $posX = $minOffset;
                $posY = $minOffset;
                break;
            case 2 : //2为顶端居中
                $posX =($groundWidth - $waterWidth) / 2;
                $posY = $minOffset;
                break;
            case 3 : //3为顶端居右
                $posX = $groundWidth - $waterWidth - $minOffset;
                $posY = $minOffset;
                break;
            case 4 : //4为中部居左
                $posX = $minOffset;
                $posY =($groundHeight - $waterHeight) / 2;
                break;
            case 5 : //5为中部居中
                $posX =($groundWidth - $waterWidth) / 2;
                $posY =($groundHeight - $waterHeight) / 2;
                break;
            case 6 : //6为中部居右
                $posX = $groundWidth - $waterWidth - $minOffset;
                $posY =($groundHeight - $waterHeight) / 2;
                break;
            case 7 : //7为底端居左
                $posX = $minOffset;
                $posY = $groundHeight - $waterHeight - $minOffset;
                break;
            case 8 : //8为底端居中
                $posX =($groundWidth - $waterWidth) / 2;
                $posY = $groundHeight - $waterHeight - $minOffset;
                break;
            case 9 : //9为底端居右
                $posX = $groundWidth - $waterWidth - $minOffset;
                $posY = $groundHeight - $waterHeight - $minOffset;
                break;
            default: //指定
                $posX = !empty($option['px']) ? ($option['px'] > 0 ? $option['px'] : $groundWidth + $option['px']) : $minOffset;
                $posY = !empty($option['py']) ? ($option['py'] > 0 ? $option['py'] : $groundHeight + $option['py']) : $minOffset;
                break;
        }

        $posX = max(0, $posX);
        $posY = max(0, $posY);
        return array('px' =>$posX , 'py' =>$posY);
    }

    /**
     * 根据文件扩展名获取图片类型
     * @param <type> $ext
     */
    public static function getTypeByExt($ext) {

        $type = false;
        switch (strtolower($ext)) {
            case 'gif':
                $type = self::TYPE_GIF;
                break;
            case 'jpg':
            case 'jpeg':
                $type = self::TYPE_JPG;
                break;
            case 'png':
                $type = self::TYPE_PNG;
                break;
        }
        return $type;
    }

    /**
     * 根据类型获取后缀
     * @param <type> $type
     */
    public static function getExtByType($type) {

        $ext = false;
        switch ($type) {
            case self::TYPE_GIF;
                $ext = 'gif';
                break;
            case self::TYPE_JPG;
                $ext = 'jpg';
                break;
            case self::TYPE_PNG;
                $ext = 'png';
                break;
        }
        return $ext;
    }

    /**
     * 生成验证码
     * @param <type> $length
     */
    public static function generateCaptchaCode($length = 4) {

        $length = max(3, min(intval($length), 20));
        $letters = 'bcdfghjkmnpqrstvwxyz23456789';
        $vowels = 'aeiu';
        $code = '';
        for($i = 0; $i < $length; ++$i) {
            if($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 3)];
            } else {
                $code .= $letters[mt_rand(0, 27)];
            }
        }

        return $code;
    }
}