<?php
/**
 * 视图
 */

class View extends Component {

    /**
     * render 目标： 空
     */
    const RENDER_NONE = 0;

    /**
     * render 目标： web response
     */
    const RENDER_WEB_RESPONSE = 1;

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