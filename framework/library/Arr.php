<?php
/**
 * 数组操作
 * 因为和 Array 冲突，改用缩写名
 */

class Arr {

    /**
	 * fetchCols
     * 获取二维数组中指定的列
     * 常用于从返回的表记录数组里获取指定列值
	 *
	 * @param  array  $data    必须为二维数组
	 * @param  string $keyword 所要列的键名
	 * @param  string $key     列键名
	 * @return array
	 */
	public static function fetchCols($data, $keyword, $key = null) {

		if (!is_array($data)) {
			return array();
		}

		$result = array();
		if ($key && is_string($key)) {
			foreach ($data as $value) {
				$result[$value[$key]] = self::fetch($value, $keyword);
			}
		} else {
			foreach ($data as $value) {
				$result[] = self::fetch($value, $keyword);
			}
		}
		return $result;
	}

    /**
     * fetch
     * 从数组中获取指定 key 对应的值
     *
     * @param array $array
     * @param array $keys
     * @param mixed $default 当 key 不存在时的默认值
     * @return array
     */
    public static function fetch($array, $keys, $default = null) {

        if (!$keys) {
            return $array;
        }

        if (!is_array($keys)) {
            return array_key_exists($keys, $array) ? $array[$keys] : $default;
        }

        $return = array();
        foreach ($keys as $key) {
            $return[$key] = array_key_exists($key, $array) ? $array[$key] : $default;
        }
        return $return;
    }
}