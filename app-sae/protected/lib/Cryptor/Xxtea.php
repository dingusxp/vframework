<?php

/**
 * xxtea 加密方式
 */
class Cryptor_Xxtea extends Cryptor_Abstract {

    private function _long2str($v, $w) {
        
        $len = count($v);
        $n = ($len - 1) << 2;
        if ($w) {
            $m = $v[$len - 1];
            if (($m < $n - 3) || ($m > $n)) return false;
            $n = $m;
        }
        $s = array();
        for ($i = 0; $i < $len; $i++) {
            $s[$i] = pack("V", $v[$i]);
        }
        if ($w) {
            return substr(join('', $s), 0, $n);
        }
        else {
            return join('', $s);
        }
    }

    private function _str2long($s, $w) {

        $v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
        if ($w) {
            $v[count($v)] = strlen($s);
        }
        return $v;
    }

    public function encrypt($str, $skey = '') {

        if ($str == "") {
            return "";
        }
        $v = $this->_str2long($str, true);
        $k = $this->_str2long($skey, false);
        $max=count($k);
        $i=0;
        $j=0;
        while(isset($v[$i])) {
            if($v[$i]==0) {
                $i++;
                continue;
            }
            $v[$i]  =   $v[$i] ^ $k[$j];
            $i++;
            $j++;
            if($j==$max) {
                $i--;
                $v[$i]  =   $v[$i]  ^   $i;
                $i++;
                $j=0;
            }
        }

        return base64_encode($this->_long2str($v, false));
    }

    public function decrypt($str, $skey = '') {
        
        if ($str == "") {
            return "";
        }
        $str = base64_decode($str);
        $v = $this->_str2long($str, false);
        $k = $this->_str2long($skey, false);
        $max=count($k);
        $i=0;
        $j=0;
        while(isset($v[$i])) {
            if($v[$i]==0) {
                $i++;
                continue;
            }
            $v[$i]  =   $v[$i] ^ (isset($k[$j]) ? $k[$j] : 0);
            $i++;
            $j++;
            if($j==$max) {
                $i--;
                $j--;
                $v[$i]  =   $v[$i] ^ $k[$j];
                $v[$i]  =   $v[$i] ^ $i;
                $v[$i]  =   $v[$i] ^ $k[$j];
                $i++;
                $j=0;
            }
        }
        return $this->_long2str($v, true);
    }
}