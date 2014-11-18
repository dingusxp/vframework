<?php
/**
 * 字符串相关一些常用函数
 */

class Str {

    /**
     * stripSlashes
     * 取消转义，兼容数组
     * 
     * @param <type> $string
     * @return <type>
     */
    public static function stripSlashes($string) {
        
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::stripSlashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     * 字符串 string 中是否包含 seek 的字符串
     * @param <type> $string
     * @param <type> $seek
     * @param <type> $caseInsensitive
     */
    public static function has($string, $seek, $caseInsensitive = false) {

        return $caseInsensitive ?
            (false !== stripos($string, $seek)) :
            (false !== strpos($string, $seek));
    }

    /**
     * json_encode 变量
     * 对中文保留原编码，而不是被转为 \uxxxx 形式
     */
    public static function jsonEncode($object) {

        if (version_compare ( PHP_VERSION ,  '5.4.0' ) >=  0) {
            return json_encode($object, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        require_once dirname(__FILE__) . '/Str/functions.php';
        return str_json_encode($object);
    }

    /**
     * 字符串哈希为整数
     * @param <type> $s
     * @param <type> $mod 
     */
    public static function hash($s, $mod = 0) {

        $hash = 5381;
        $o = 0;
        $l = strlen($s);
        for (; $l >= 8; $l -= 8) {
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
            $hash = ($hash << 5) + $hash + ord($s[$o++]);
        }
        switch ($l) {
            case 7: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 6: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 5: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 4: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 3: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 2: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 1: $hash = ($hash << 5) + $hash + ord($s[$o++]);
            case 0: break;
        }

        $hash = $hash & PHP_INT_MAX;
        $mod = intval($mod);
        return $mod > 0 ? $hash % $mod : $hash;
    }
}