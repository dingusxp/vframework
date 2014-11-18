<?php

/**
 * Mysql 数据库操作
 */
class DB_Mysql extends DB_Abstract {

    /**
     * 连接
     * @var <type>
     */
    private static $_conns = array();

    /**
     * db handler
     * @var <type>
     */
    private $_dbh = null;

    /**
     * dsn
     * @var <type>
     */
    private $_dsn = '';

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

        parent::__construct($option);
    }

    /**
     * 连接到数据库
     *
     * @param array $option
     * @return
     */
    public function connect(array $option = array()) {

        // 必需指定数据库
        if (empty($option['dbname'])) {
            throw new DB_Exception('Not specified dbname', DB_Exception::E_DB_BAD_CONNECTION_PARAM);
        }

        // 默认参数
        $option = $option + array(
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
        );

        // 初始化
        $this->_dsn = 'mysql:host='.$option['host'].';dbname='.$option['dbname'];
        if (!array_key_exists($this->_dsn, self::$_conns)){
            $this->_dbh = mysql_connect($option['host'], $option['user'], $option['password']);
            if (!$this->_dbh) {
                throw new DB_Exception('Connect to mysql failed: ' . $this->_dsn, DB_Exception::E_DB_CONNECT_FAILED);
            }
            $this->_query('SET NAMES UTF8 ');
            mysql_select_db($option['dbname'], $this->_dbh);
            self::$_conns[$this->_dsn] = $this->_dbh;
        }

        $this->_dbh = self::$_conns[$this->_dsn];
    }

    /**
     * 开启事物
     */
    public function begin() {

        if ($this->_beginTransaction) {
            return;
        }
        $this->_query('SET AUTOCOMMIT=0');
        $this->_query('BEGIN');
        $this->_beginTransaction = true;
    }

    /**
     * 提交事务
     */
    public function commit() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_query('COMMIT');
        $this->_query('SET AUTOCOMMIT=1');
        $this->_beginTransaction = false;
    }

    /**
     * 回滚事务
     */
    public function rollBack() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_query('ROLLBACK');
        $this->_query('SET AUTOCOMMIT=1');
        $this->_beginTransaction = false;
    }

    /**
     * 拼接 sql
     * @param <type> $query
     * @param <type> $params
     * @return <type>
     */
    public function _makeSql($query, $params = array()) {

        $params = array_map('mysql_escape_string', $params);
        $query = str_replace('?', "'%s'", $query);
        return vsprintf($query, $params);
    }

    /**
     * 取得记录的第一行
     * @param sql string $query
     * @param array $params
     */
    public function fetchRow($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        $result = $this->_query($query);
        return mysql_fetch_assoc($result);
    }

    /**
     * 取得所有的记录
     * @param sql string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        $result = $this->_query($query);
        $rows = array();
        while(($row = mysql_fetch_assoc($result))) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 获取记录的第一行第一列
     * @param string sql $query
     * @param array $params
     */
    public function fetchOne($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        $result = $this->_query($query);
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    /**
     * 执行sql 语句
     * @param sqlstring $query
     * @param array $params
     * @return 更新的记录的条数
     */
    public function execute($query, $params = array()) {

        $query = $this->_makeSql($query, $params);
        $this->_query($query);
        return mysql_affected_rows($this->_dbh);
    }

    /**
     * 执行 mysql 查询
     * @param <type> $query
     * @return <type>
     */
    private function _query($query) {

        $result = mysql_query($query, $this->_dbh);

        // 检查错误
        if (mysql_errno($this->_dbh) !== 0) {
            throw new DB_Exception(V::t('SQL query failed: {msg}', array('msg' => mysql_error($this->_dbh)), 'framework'),
                DB_Exception::E_DB_QUERY_FAILED);
        }

        return $result;
    }

    /**
     * 获取最后一条记录的id
     * @return string
     */
    public function lastInsertId() {

        return mysql_insert_id($this->_dbh);
    }

    /**
     * 关闭数据库连接
     * @param string $dsn
     */
    public function close() {

        $this->_dbh = null;
        unset(self::$_conns[$this->_dsn]);
    }

    /**
     * 过滤 sql 非法字符
     * @param type $value
     * @return type
     */
    public function escape($value) {

        return mysql_real_escape_string($value, $this->_dbh);
    }
}