<?php

/**
 * V_Exception
 */
class V_Exception extends Exception {

    /**
     * 异常类型
     */
    protected $_type = null;

    /**
     * 继承层级
     * @var type
     */
    protected $_level = 0;

    /**
     * 由下级未做处理直接传递过来的异常
     */
    const E_PASSON_EXCEPTION = 987654321;

    /**
     * 通用异常码： 参数错误
     */
    const E_PARAM_ERROR = 2;

    /**
     * 通用异常码： 逻辑错误
     */
    const E_LOGIC_ERROR = 1;

    /**
     * 异常： 引导器不能运行；
     */
    const E_BOOTSTRAP_CANNOT_RUN = 11;

    /**
     * 异常： 引导器已经在运行；
     */
    const E_BOOTSTRAP_INIT_ERROR = 12;

    /**
     * 构造函数
     * @param <type> $message
     * @param <type> $code
     */
    public function __construct($message, $code = 0, Exception $previous = null) {

        if ($message instanceof Exception) {
            parent::__construct($message->getMessage(), self::E_PASSON_EXCEPTION, $previous);
        } else {
            parent::__construct($message, intval($code), $previous);
        }
        $this->_level++;
    }

    /**
     * 获取异常类型：即 类名前缀小写
     * 如 DAO_Exception ，返回 dao
     *
     * @return string
     */
    public function getType() {

        if (null === $this->_type) {
            $this->_type = str_replace('_exception', '', strtolower(get_class($this)));
        }

        return $this->_type;
    }

    /**
     * 获取异常继承的层级
     *
     * @return int
     */
    public function getLevel() {

        return $this->_level;
    }
}