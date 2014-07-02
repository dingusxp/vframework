<?php


/**
 * 调试器
 */
class Debugger {

    /**
     * 时间线输出模式：网页代码
     */
    const TIMELINE_HTML = 1;

    /**
     * 时间线输出模式：文本
     */
    const TIMELINE_PLAIN = 2;

    /**
     * 输出变量模式：var_export 函数打印
     */
    const OUTPUT_VAR_EXPORT = 1;

    /**
     * 输出变量模式：print_r 函数打印
     */
    const OUTPUT_PRINT_R = 2;

    /**
     * 输出变量模式：var_dump 函数打印
     */
    const OUTPUT_VAR_DUMP = 3;

    /**
     * Debugger 实例
     * @var <type>
     */
    private static $_instance = null;

    /**
     * 时间线记录
     * @var <type>
     */
    private $_timelines = array();

    /**
     * 获取实例
     * @return <type>
     */
    public static function getInstance() {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 构造函数
     */
    public function  __construct() {

    }

    /**
     * 添加时间记录
     * @param <type> $message
     */
    public function timeline($message) {

        $this->_timelines[] = array(
            'message' => $message,
            'time' => V::runtime(),
        );
    }

    /**
     * 输出时间线信息
     * @param <type> $mode
     */
    public function showTimeline($mode = self::TIMELINE_HTML, $param = array()) {

        switch ($mode) {
            case self::TIMELINE_HTML:

                // 使用内置模板配置
                $view = View::factory(V::config('v.view'));
                $view->assign('param', (array)$param);
                $view->assign('timelines', $this->_timelines);
                echo $view->render('timeline', View::RENDER_NONE);
                break;
            default:
                echo PHP_EOL, 'Timeline:', PHP_EOL;
                foreach ($this->_timelines as $timeline) {
                    echo $timeline['time'], "ms\t", $timeline['message'], PHP_EOL;
                }
        }
    }

    /**
     * 输出变量
     * @param <type> $var
     */
    public function output($var, $terminate = false, $mode = self::OUTPUT_VAR_EXPORT) {

        // 输出
        switch ($mode) {
            case self::OUTPUT_PRINT_R:
                print_r($var);
                break;
            case self::OUTPUT_VAR_DUMP:
                var_dump($var);
                break;
            default:
                var_export($var);
                break;
        }

        // 终止
        if ($terminate) {
            exit;
        }
    }

}