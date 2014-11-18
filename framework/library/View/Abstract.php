<?php
/**
 * View 视图
 */
abstract class View_Abstract {

    /**
     * 数据
     * @var <type>
     */
    protected $_data = array();

    /**
     * 配置参数
     * @var <type>
     */
    protected $_option ;

    /**
     * 构造函数
     * @param array $option
     */
    public function  __construct(array $option = array()) {

        $this->_option = array_merge(
                array(
                    'template_ext' => 'tpl',
                    'template_dir' => APP_PATH . '/template',
                    'compile_dir' => APP_PATH . '/tmp/template_c',
                    ),
                $option
                );
    }

    /**
     * 视图数据
     * @param <type> $key
     * @param <type> $value
     */
   public function assign($key, $value = null) {

        if (is_array($key)) {
            foreach($key as $k => $v) {
                $this->assign($k, $v);
            }
        } else {
            $this->_data[$key] = $value;
        }
    }

    /**
     * 获取编译之后的文件
     * @param <type> $tpl
     * @return <type>
     */
    protected function _getCompiledFile($tpl) {

        return FS::joinPath($this->_option['template_c_dir'], $tpl . '.php');
    }

    /**
     * 视图渲染
     * @param <type> $tpl
     * @param <type> $target
     * @return <type>
     */
    public function render($tpl, $target = View::RENDER_WEB_RESPONSE) {

        $filename = $this->_getCompiledFile($tpl);
        if (!is_file($filename)) {
            throw new View_Exception(V::t('Compile file not found: {name}', array('name' => $filename), 'framework'), View_Exception::COMPILE_FILE_NOT_FOUND);
        }

        // PHP 解析
        ob_start();
        extract($this->_data);
        include($filename);
        $content = ob_get_contents();
        ob_end_clean();

        // render
        if ($target == View::RENDER_WEB_RESPONSE) {
            Web_Response::getInstance()->appendBody($content);
        }
        
        return $content;
    }
}