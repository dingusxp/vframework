<?php

/**
 * 文件日志
 */
class Logger_File extends Logger_Abstract {

    /**
     * 日志保存路径
     * @var <type>
     */
    private $_path = '';

    /**
     * 按日期归档的日期格式
     * @var <type>
     */
    private $_dateFormat = '';

    /**
     * 文件名称映射
     * @var <type>
     */
    private $_filenameMapping = array();

    /**
     * 构造函数
     * @param <type> $option
     */
    public function  __construct($option = array()) {

        if (!empty($option['path'])) {
            $this->_path = $option['path'];
        } else {
            $this->_path = FS::joinPath(APP_PATH, 'log');
        }

        if (!empty($option['date_format'])) {
            $this->_dateFormat = $option['date_format'];
        }

        if (!empty($option['log_filename'])) {
            $this->_filenameMapping = $option['log_filename'];
        }
    }

    /**
     * 保存日志到文件
     * @param <type> $logId
     * @param <type> $type
     * @param <type> $message
     * @param <type> $logParam
     * @return <type>
     */
    protected function _save($logId, $type, $message, $logParam = array()) {

        $name = !empty($this->_filenameMapping[$type]) ? $this->_filenameMapping[$type] : 'unknown';
        if ($this->_dateFormat) {
            $name = $name.'.'.date($this->_dateFormat);
        }
        $name = $name.'.log';
        $filename = FS::joinPath($this->_path, $name);

        $datetime = date('Y-m-d H:i:s');
        $typeName = Logger::getTypeName($type);
        $content = "$logId - $typeName - $datetime | ";
        if (is_array($message)) {
            $message = Str::jsonEncode($message);
        }
        $content .= $message;
        $content .= PHP_EOL;
        if ($logParam) {
            $content .= '(PARAM)'.Str::jsonEncode($logParam);
            $content .= PHP_EOL;
        }
        return FS::write($filename, $content, true);
    }
}