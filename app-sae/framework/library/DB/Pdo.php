<?php

/**
 * DB PDO
 */
class DB_Pdo extends DB_Abstract {

    /**
     * 连接
     * @var <type>
     */
    private static $_conns = array();

    /**
     * db handler
     * @var <type>
     */
    private $_dbh;

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
        if (empty($option['dsn'])) {
            throw new DB_Exception('Not specified dsn', DB_Exception::E_DB_BAD_CONNECTION_PARAM);
        }

        // 默认参数
        $option = $option + array(
            'user' => 'root',
            'password' => '',
        );

        // 初始化
        $this->_dsn = $option['dsn'];
        if (!array_key_exists($this->_dsn, self::$_conns)){
            $attr = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );
            try {
                $this->_dbh = new PDO($option['dsn'], $option['user'], $option['password'], $attr);
                $this->_dbh->exec('SET NAMES UTF8');
                self::$_conns[$this->_dsn] = $this->_dbh;
            } catch (PDOException $e) {
                throw new DB_Exception('Connect to db failed: ' . $this->_dsn, DB_Exception::E_DB_CONNECT_FAILED);
            }
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
        $this->_dbh->beginTransaction();
        $this->_beginTransaction = true;
    }

    /**
     * 提交事务
     */
    public function commit() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_dbh->commit();
        $this->_beginTransaction = false;
    }

    /**
     * 回滚事务
     */
    public function rollBack() {

        if (false == $this->_beginTransaction) {
            return;
        }
        $this->_dbh->rollBack();
        $this->_beginTransaction = false;
    }

    /**
     * 取得记录的第一行
     *
     * @param sql string $query
     * @param array $params
     */
    public function fetchRow($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 取得所有的记录
     *
     * @param sql string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取记录的第一行第一列
     *
     * @param string sql $query
     * @param array $params
     */
    public function fetchOne($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        $result = $stmt->execute($params);
        if ($result) {
            return $stmt->fetchColumn();
        }
        return null;
    }

    /**
     * 执行sql 语句
     *
     * @param sqlstring $query
     * @param array $params
     * @return 更新的记录的条数
     */

    public function execute($query, $params = array()) {

        $stmt = $this->_dbh->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * 获取最后一条记录的id
     *
     * @return string
     */
    public function lastInsertId() {

        return $this->_dbh->lastInsertId();
    }

    /**
     * 关闭数据库连接
     * @param string $dsn
     */
    public function close() {

        $this->_dbh = null;
        unset(self::$_conns[$this->_dsn]);
    }
}