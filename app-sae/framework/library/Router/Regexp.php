<?php

/**
 * 正则规则路由
 * 通过正则规则指定链接对应 action
 */
class Router_Regexp extends Router_Abstract {

    /**
     * _parse
     * 路由解析
     * rule 格式：
     *   pattern => '',
     *   action => 'action',
     *   param => array('controller' => '\\1'),
     *   req => array('id' => '\\1')
     */
    public function  parse() {

        // 遍历规则，查找匹配的路由
        $rules = $this->_option['rules'];
        $searchPattern = '{{index}}';
        if (isset($this->_option['search_pattern'])) {
            $searchPattern = $this->_option['search_pattern'];
        }
        $path = $_SERVER['REQUEST_URI'];
        $maxReplace = 9;
        foreach ($rules as $rule) {

            // 如果匹配到
            $match = array();
            if (preg_match($rule['pattern'], $path, $match)) {
                $action = array('operation' => $rule['operation']);
                $search = $replace = array();
                for ($i = 0; $i <= $maxReplace; $i++) {
                    $search[] = str_replace('{index}', $i, $searchPattern);
                    $replace[] = isset($match[$i]) ? $match[$i] : '';
                }
                if (!empty($rule['param'])) {
                    foreach ($rule['param'] as $k => $v) {
                        $action['param'][$k] = str_replace($search, $replace, $v);
                    }
                }
                if (!empty($rule['req'])) {
                    foreach ($rule['req'] as $k => $v) {
                        $_GET[$k] = str_replace($search, $replace, $v);
                    }
                }

                return $action;
            }
        }

        return array();
    }
}