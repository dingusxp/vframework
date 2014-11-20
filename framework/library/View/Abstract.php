<?php
/**
 * View 视图
 */
abstract class View_Abstract {
    
    /**
     * 数据
     * @var array
     */
    protected $_data = array();

    /**
     * 配置参数
     * @var array
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
     * @param string $key
     * @param mixed $value
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
     * @param string $tpl
     * @return string
     */
    protected function _getCompiledFile($tpl) {

        return FS::joinPath($this->_option['template_c_dir'], $tpl . '.php');
    }

    /**
     * 视图渲染
     * @param string $tpl
     * @param string $target
     * @return string
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
    
    /**
     * 将模块解析后放置到 layout 渲染
     * @param array/string $moduleTpls
     * @param string $layoutTpl
     * @param array $tplData
     * @param int $option
     * @param int $target
     */
    public function renderLayout($moduleTpls, $layoutTpl, $tplData = array(), $option = 0, $target = View::RENDER_WEB_RESPONSE ) {

        if (!is_array($moduleTpls)) {
            $moduleTpls = array('mainContent' => $moduleTpls);
        }
        
        // 参数指定变量
        if ($tplData) {
            foreach ($tplData as $key => $value) {
                $this->assign($key, $value);
            }
        }

        $extraScript = $extraStyle = '';
        foreach ($moduleTpls as $name => $tpl) {
            $content = $this->render($tpl, View::RENDER_NONE);
            
            // 提取模块中的 css、js，放置到 layout 指定位置
            if ($option & View::LAYOUT_REBUILD_RESOURCE) {
                $matches = array();
                if (preg_match_all('/\<link rel\=\"stylesheet\".*?\/\>\s*/i', $content, $matches)) {
                    foreach ($matches[0] as $value) {
                        $extraStyle .= $value;
                        $content = str_replace($value, '', $content);
                    }
                }
                if (preg_match_all('/\<style type\=\"text\/css\".*?\<\/style\>\s*/ims', $content, $matches)) {
                    foreach ($matches[0] as $value) {
                        $extraStyle .= $value;
                        $content = str_replace($value, '', $content);
                    }
                }
                if (preg_match_all('/\<script type\=\"text\/javascript\" src\=\".*?\<\/script\>\s*/i', $content, $matches)) {
                    foreach ($matches[0] as $value) {
                        $extraScript .= $value;
                        $content = str_replace($value, '', $content);
                    }
                }
                if (preg_match_all('/\<script type\=\"text\/javascript"\>.*?\<\/script>\s*/ims', $content, $matches)) {
                    foreach ($matches[0] as $value) {
                        $extraScript .= $value;
                        $content = str_replace($value, '', $content);
                    }
                }

                // TO-DO 合并去除多余的 script 和 style 标签
                
            }
            
            $this->assign($name, $content);
        }
        
        $this->assign('extraStyle', $extraStyle);
        $this->assign('extraScript', $extraScript);
        return $this->render($layoutTpl, $target);
    }
}