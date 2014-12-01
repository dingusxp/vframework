<?php

/**
 * 缓存类
 */
class Cache extends Component {

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return Cache_Abstract
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
}
