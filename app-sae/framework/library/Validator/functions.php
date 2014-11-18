<?php
/**
 * 验证系列函数
 */

/**
 * 判断是否一个合法的时间表示
 * @param type $dateTime
 * @return boolean
 */
function validator_datetime($dateTime) {

    $matches = array();
    if (preg_match("/^(\d{4})-(\d{1,2})-(\d{1,2})( ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))?$/", $dateTime, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }

    return false;
}

/**
 * 判断指定值是否在给定值之间
 * @param type $o
 * @param type $min
 * @param type $max
 * @return type
 */
function validator_between($o, $min, $max) {

    return $o >= $min && $o <= $max;
}