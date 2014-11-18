<?php

/**
 * 与 key 做异或的方法加密解密
 * 可以设置多重加（解）密 key
 */
class Cryptor_Xor extends Cryptor_Abstract {

    /**
     * 多轮加（解）密 key
     * @var <type>
     */
    private $_keys = array();

    /**
     * 构造函数，可设置 keys
     * @param <type> $option
     */
    public function  __construct(array $option = array()) {
        
        parent::__construct();

        if (isset($option['keys'])) {
            foreach ((array)$option['keys'] as $skey) {
                $this->_addKey($skey);
            }
        }
    }

    /**
     * 添加一个 key
     * @param string $skey
     */
    public function _addKey($skey) {

        if (strlen($skey)) {
            array_push($this->_keys, $skey);
        }
    }

    /**
     * 加密字符串
     * 
     * @param <type> $string
     * @param <type> $skey
     * @return string
     */
    public function encrypt($string, $skey = '') {

        $keys = $this->_keys;
        if (strlen($skey)) {
            array_push($keys, $skey);
        }
        
        for ($i = 0, $L = count($keys); $i < $L; $i++) {
            $string = $this->_crypt($string, $keys[$i]);
        }
        return base64_encode($string);
    }

    /**
     * 解密字符串
     * @param <type> $string
     * @param <type> $skey
     * @return string
     */
    public function decrypt($string, $skey = '') {

        $keys = $this->_keys;
        if (strlen($skey)) {
            array_push($keys, $skey);
        }
        
        $keys = array_reverse($keys);
        $string = base64_decode($string);
        for ($i = 0, $L = count($keys); $i < $L; $i++) {
            $string = $this->_crypt($string, $keys[$i]);
        }
        return $string;
    }

    /**
     * 两字符串按位异或
     * @param <type> $text
     * @param <type> $key
     * @return <type>
     */
    public function _crypt($text, $key) {

        $len = strlen($key);
        $k = 0;
        $tmp = '';
        for ($i=0, $L = strlen($text); $i < $L; $i++) {
             if ($k == $len) {
                 $k = 0;
             }
             $tmp .=  substr($text, $i, 1) ^ substr($key, $k, 1);
             $k++;
        }
        return $tmp;
    }
}