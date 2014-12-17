<?php
/**
 * Smarty 风格模版引擎
 *
 */

class View_Smarty extends View_Abstract {

	private $_smarty = null;

	private $_extension = 'tpl';

    /**
     * 构造函数
     *
     **/
    public function __construct(array $option = array()) {

        $option = array_merge(
                array(
                    'template_dir' => APP_PATH . '/template',
                    'compile_dir' => APP_PATH . '/tmp/template_c',
                    'force_compile' => false,
                    'template_ext' => 'tpl',
                ),
                $option
            );
        $this->_extension = $option['template_ext'];
		require_once dirname(__FILE__).'/Smarty/Smarty.class.php';
        $this->_smarty = new Smarty();
        foreach ((array)$option as $key => $value) {
            if (isset($this->_smarty->$key)) {
                $this->_smarty->$key = $value;
            }
		}
        parent::__construct($option);
    }

    /**
     * 视图渲染
     * @param <type> $tpl
     * @param <type> $return
     * @return <type>
     */
    public function render($tpl, $target = View::RENDER_WEB_RESPONSE) {

        foreach ($this->_data as $key => $value) {
            $this->_smarty->assign($key, $value);
        }
        $content = $this->_smarty->fetch($tpl . '.' . $this->_extension);

        // render
        if ($target == View::RENDER_WEB_RESPONSE) {
            Web_Response::getInstance()->appendBody($content);
        }

        return $content;
    }
}