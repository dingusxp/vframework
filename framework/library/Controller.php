<?php

/**
 * 控制器
 */
class Controller extends Component {

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return Controller_Abstract
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
}