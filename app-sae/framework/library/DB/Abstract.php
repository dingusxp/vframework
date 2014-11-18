<?php

/**
 * DB 基类
 */
abstract class DB_Abstract {

    /**
     * db 构造函数
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     */
    public function __construct(array $option = array()){

        if ($option) {
            $this->connect($option);
        }
    }

    /**
     * 连接数据库
     */
    abstract function connect(array $option = array());

    /**
     * 开启事物
     */
    abstract public function begin();

    /**
     * 提交事务
     */
    abstract public function commit();

    /**
     * 回滚事务
     */
    abstract public function rollBack();

    /**
     * 取得记录的第一行
     *
     * @param sql string $query
     * @param array $params
     */
    abstract public function fetchRow($query, $params = array());

    /**
     * 取得所有的记录
     *
     * @param sql string $query
     * @param array $params
     * @return array
     */
    abstract public function fetchAll($query, $params = array());

    /**
     * 获取记录的第一行第一列
     *
     * @param string sql $query
     * @param array $params
     */
    abstract public function fetchOne($query, $params = array());

    /**
     * 执行sql 语句
     *
     * @param sqlstring $query
     * @param array $params
     * @return 操作影响的行数
     */
    abstract public function execute($query, $params = array());

    /**
     * 获取最后一条记录的id
     * @return string
     */
    abstract public function lastInsertId();

    /**
     * 关闭数据库连接
     * @param string $dsn
     */
    abstract public function close();

    /**
     * 过滤 sql 中非法字符
     * @param string $value
     */
    public function escape($value) {
        
        return addslashes($value);
    }
}