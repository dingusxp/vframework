<?php
/**
 * Str 辅助函数
 */

/**
 * json_encode 保留中文（不转换为 \uxxxx 形式）
 *
 **/
function str_json_encode($value) {

    if (is_object($value)) {
        $value = get_object_vars($value);
    }

    $value = _str_urlencode($value);
    $json = json_encode($value);
    return urldecode($json);
}
function _str_urlencode($value) {

    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $k2 = _str_urlencode($k);
            if ($k != $k2) {
                unset($value[$k]);
                $k = $k2;
            }
            $value[$k] = _str_urlencode($v);
        }
    } elseif (is_string($value)) {
		$value = str_replace(array("\\", "\r\n", "\r", "\n", "\"", "/", "\t"),
						   array('\\\\', '\\n', '\\n', '\\n', '\\"', '\\/', '\\t'),
						   $value);
        $value = urlencode($value);
    }

    return $value;
}