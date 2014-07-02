<?php
/**
 * 日志记录 抽象类
 */
abstract class Logger_Abstract {

    /**
     * 保存日志
     * @param <type> $type
     * @param <type> $message
     * @param <type> $datetime
     */
    abstract protected function _save($logId, $type, $message, $logParam);

    /**
     * 记录日志
     * @param <type> $message
     * @param <type> $type
     * @param <type> $extra
     * @param <type> $cate
     */
    public function log($type, $message) {

        if ($message instanceof Exception) {
            $e = $message;
            $message = 'Exception: ' . $e->getMessage() . PHP_EOL
                    . '(TRACE)' . $e->getTraceAsString();
        }

        $logId = Logger::getLogId();
        $logParam = Logger::getParam();
        $this->_save($logId, $type, $message, $logParam);
    }
}