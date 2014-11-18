<?php

/**
 * 数据库操作
 */
class DB extends Component {

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
}