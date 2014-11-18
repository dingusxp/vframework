<?php
/**
 * 计时器
 */

class Timer {
    
    /**
     * 实例
     * @var type 
     */
    private static $_instances = array();

    /**
     * 时间点
     * @var type
     */
    private $_times = array();
    
    /**
     * 获取一个指定时间的 timer 计时器
     * @param type $name
     */
    public static function getInstance($name = 'system') {
        
        if (!isset(self::$_instances[$name])) {
            self::$_instances[$name] = new self();
        }
        return self::$_instances[$name];
    }
    
    /**
     * 构造函数
     */
    private function __construct() {

    }

    /**
     * 增加一个记录点
     * @param type $msg
     */
    public function log($msg = '') {
        
        $this->_times[] = array(microtime(true), $msg);
    }

    /**
     * 输出
     * @param type $file
     */
    public function output($file = null) {

        $rows = array();
        $rows[] = '<pre>';
        $l = count($this->_times);
        for ($i = 0; $i < $l; $i++) {
            $diff = 0;
            if ($i > 0) {
                $diff = 1000 * ($this->_times[$i][0] - $this->_times[$i - 1][0]);
            }
            $rows[] = sprintf('%.3f - %s (%d ms)', $this->_times[$i][0], $this->_times[$i][1], $diff);
        }
        $rows[] = '===============';
        $rows[] = 'total:' . intval(1000 * ($this->_times[$l - 1][0] - $this->_times[0][0])) . ' ms';
        $rows[] = '</pre>';
        $rows[] = "\n";
        $content = implode("\n", $rows);
        if ($file) {
            FS::write($file, $content, true);
        } else {
            echo $content;
        }
    }
}