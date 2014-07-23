<?php

/**
 * DB SaeMysql
 */
class DB_SaeMysql extends DB_Abstract {

    /**
     * sae 对象
     * @var <type>
     */
    private static $_object = null;

    /**
     * db handler
     * @var <type>
     */
    private $_dbh;

    /**
     * 是否开启了事物
     * @var <type>
     */
    private $_beginTransaction = false;

    /**
     * db 构造函数
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     */
    public function __construct(array $option = array()) {

        $option = $option + array(
                                'version' => 'unknown',
                            );
        parent::__construct($option);
    }

    /**
     * 连接到数据库
     *
     * @param array $option
     * @return 
     */
    public function connect(array $option = array()) {

        if (self::$_object === null) {
            self::$_object = new SaeMysql();
        }

        $this->_dbh = self::$_object;
        return $this->_dbh;
    }

    /**
     * 开启事物
     */
    public function begin() {

        if ($this->_beginTransaction) {
            return;
        }
        $this->_dbh->runSql('SET AUTOCOMMIT=0');
        $this->_dbh->runSql('BEGIN');
        $this->_beginTransaction = true;
    }

    /**
     * 提交事务
     */
    public function commit() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_dbh->runSql('COMMIT');
        $this->_dbh->runSql('SET AUTOCOMMIT=1');
        $this->_beginTransaction = false;
    }

    /**
     * 回滚事务
     */
    public function rollBack() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_dbh->runSql('ROLLBACK');
        $this->_dbh->runSql('SET AUTOCOMMIT=1');
        $this->_beginTransaction = false;
    }

    public function _makeSql($query, $params = array()) {

        $params = array_map(array($this->_dbh, 'escape'), $params);
        $query = str_replace('?', "'%s'", $query);
        return vsprintf($query, $params);
    }

    /**
     * 取得记录的第一行
     *
     * @param sql string $query
     * @param array $params
     */
    public function fetchRow($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        return $this->_dbh->getLine($query);
    }

    /**
     * 取得所有的记录
     *
     * @param sql string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        return $this->_dbh->getData($query);
    }

    /**
     * 获取记录的第一行第一列
     *
     * @param string sql $query
     * @param array $params
     */
    public function fetchOne($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        return $this->_dbh->getVar($query);
    }

    /**
     * 执行sql 语句
     *
     * @param sqlstring $query
     * @param array $params
     * @return 更新的记录的条数
     */

    public function execute($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        $this->_dbh->runSql($query);
        if( $this->_dbh->errno() != 0 ) {
            throw new DB_Exception('Query failed:' . $this->_dbh->errmsg(), DB_Exception::E_DB_QUERY_FAILED);
        }
        return $this->_dbh->affectedRows();
    }

    /**
     * 获取最后一条记录的id
     *
     * @date 10/21/2009
     * @return string
     */
    public function lastInsertId() {

        return $this->_dbh->lastId();
    }

    /**
     * 关闭数据库连接
     * @param string $dsn
     */
    public function close() {

    }
}