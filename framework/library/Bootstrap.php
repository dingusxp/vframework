<?php
/**
 * Bootstrap
 */
class Bootstrap extends Component {
    
    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return Bootstrap_Abstract
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }
}

