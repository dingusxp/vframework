<?php

/**
 * HTML 页面 widget
 */
class HTML_Widget extends Component {

    /**
     * 默认 view
     * @var <type>
     */
    private static $_view = null;

    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }

    /**
     * 获取默认 view
     */
    public static function getView() {

        if (null === self::$_view) {
            
            // 依次从 widget.view -> web.view -> view 加载配置
            $config = V::config('widget.view');
            if (!$config) {
                $config = V::config('web.view');
                if (!$config) {
                    $config = V::config('view');
                }
            }
            try {
                self::$_view = View::factory($config);
            } catch (Exception $e) {
                throw new HTML_Widget_Exception(V::t('Init widget view failed:{msg}', array('msg' => $e->getMessage()), 'framework'),
                        HTML_Widget_Exception::E_HTML_WIDGET_GET_VIEW_ERROR);
            }
        }
        
        return self::$_view;
    }
}