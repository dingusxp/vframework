<?php

/**
 * DAO Mysql
 */
class DAO_Mysql extends DAO_Abstract {

    /**
     * DB
     * @var <type>
     */
    protected $_db;

    /**
     * 表名
     * @var <type>
     */
    protected $_table;

    /**
     * 主键
     * @var <type>
     */
    protected $_pk;

    /**
     * 字段
     * @var array
     */
    protected $_fields;

    /**
     * 默认的字段值（插入时）
     * @var <type>
     */
    protected $_default;

    /**
     * 必须的字段（插入时）
     * @var <type>
     */
    protected $_required;

    /**
     * 需要编码存储的复杂字段
     * @var <type>
     */
    protected $_encodeFields = array();

    /**
     * 初始化表信息
     */
    public function  __construct(array $option = array()) {

        parent::__construct($option);

        // 初始化 db
        $config = $this->_config('db');
        try {
            $this->_db = DB::factory($config['engine'], $config['option']);
        } catch (Exception $e) {
            throw new DAO_Exception('Init DB error' . $e->getMessage(), 301);
        }

        // 表信息
        $config = $this->_config('table');
        if (!$config['name']) {
            throw new DAO_Exception('Not specified table', 302);
        }
        $this->_table = $config['name'];

        $this->_pk = array();
        if (!empty($config['pk'])) {
            $this->_pk = is_array($config['pk']) ? $config['pk'] : array($config['pk']);
        }

        $this->_fields = array();
        if (isset($config['fields']) && is_array($config['fields'])) {
            $this->_fields = $config['fields'];
        }

        $this->_default = array();
        if (isset($config['default']) && is_array($config['default'])) {
            $this->_default = $config['default'];
        }

        $this->_encodeFields = array();
        if (isset($config['encode_fields']) && is_array($config['encode_fields'])) {
            $this->_encodeFields = $config['encode_fields'];
        }

        $this->_required = isset($config['required']) ? $config['required'] : array();
    }

    /**
     * 设置默认值
     * @param <type> $data
     */
    protected function _setDefault($data) {

        if ($this->_default) {
            foreach ($this->_default as $field => $value) {
                if (!isset($data[$field]) || null === $data[$field]) {
                    $data[$field] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * 检查给定的字段，是否包含了必须的全部字段
     * @param <type> $fields
     */
    protected function _checkRequired($fields) {

        if ($this->_required && ($f = array_diff($this->_required, $fields))) {
            throw new DAO_Exception('Fields are required: ' . implode(', ', $f), 304);
        }
    }

    /**
     * 检查给定的字段，是否在配置的字段中
     * @param <type> $fields
     */
    protected function _checkFields($fields) {

        if ($this->_fields && ($f = array_diff($fields, $this->_fields))) {
            throw new DAO_Exception('Fields are illegal: ' . implode(', ', $f), 305);
        }
    }

    /**
     * 将数据编码为字符串
     */
    protected function _encodeValue($data) {

        return Str::jsonEncode($data);
    }

    /**
     * 解码由 _encodeValue 编码的数据
     */
    protected function _decodeValue($value) {

        return json_decode($value, true);
    }

    /**
     * 将由 _encodeFields 指定的字段编码
     * @param <type> $data
     */
    protected function _encodeData($data) {

        if ($this->_encodeFields) {
            foreach ($this->_encodeFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $this->_encodeValue($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * 将由 _encodeFields 指定的字段解码
     * @param <type> $data
     */
    protected function _decodeData($data) {

        if ($this->_encodeFields) {
            foreach ($this->_encodeFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $this->_decodeValue($data[$field], true);
                }
            }
        }

        return $data;
    }

    /**
     * 插入新数据
     * @param <type> $data
     * @return <type>
     */
    protected function _insert($data) {

        // 设置默认值
        $data = $this->_setDefault($data);

        // 编码
        $data = $this->_encodeData($data);

        // 拼接 sql
        $fields = $marks = $params = array();
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $marks[] = '?';
            $params[] = $value;
        }

        // 检查字段
        $this->_checkFields($fields);
        $this->_checkRequired($fields);

        $sql = 'INSERT INTO `' . $this->_table . '`(`' . implode('`, `', $fields) . '`) VALUES (' . implode(',', $marks) . ')';
        try {
            $result = $this->_db->execute($sql, $params);
            return $result ? $this->_db->lastInsertId() : 0;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 构造主键条件
     * @param <type> $pkValue
     * @return <type>
     */
    protected function _makePkCondition($pkValue) {

        if (!$pkValue) {
            throw new Exception('PK value does not given', 409);
        }

        $pkValue = (array)$pkValue;
        if (count($pkValue) != count($this->_pk)) {
            throw new Exception('PK key and values does not match', 410);
        }

        $return = array();
        for ($i = 0, $L = count($this->_pk); $i < $L; $i++) {
            $return['condition'][] = '`' . $this->_pk[$i] . '` = ?';
            $return['param'][] = $pkValue[$i];
        }

        return $return;
    }

    /**
     * 构造 select 后的 字段
     * @param <type> $fields
     * @return <type>
     */
    protected function _buildFields($fields) {

        if ($fields && is_array($fields)) {

            // 检查字段
            $this->_checkFields($fields);

            return '`' . implode('`, `', $fields) . '`';
        } else {
            return '*';
        }
    }

    /**
     * 生成 where 条件的 sql 语句 和 param 参数
     * @param <type> $conditions
     */
    protected function _makeCondition($conditions) {

        if (!$conditions) {
            return array('condition' => '1', 'param' => array());
        }

        // 拼 sql
        $wheres = $params = array();
        foreach ($conditions as $key => $value) {
			if (is_int($key)) {
				$wheres[] = $value;
			} elseif (is_array($value)) {
                $wheres[] = "`$key` $value[0] ?";
                $params[] = $value[1];
            } else {
                $wheres[] = "`$key` = ?";
                $params[] = $value;
            }
        }

        return array('condition' => implode(' AND ', $wheres), 'param' => $params);
    }

    /**
     * 处理 order by 部分
     * @param <type> $orderBy
     */
    protected function _makeOrderBy($orderBy) {

        if ($orderBy) {
            if (is_array($orderBy)) {
                $orderByArr = array();
                foreach ($orderBy as $k => $v) {
                    if (is_int($k)) {
                        $orderByArr[] = $v;
                    } else {
                        $v = strtoupper($v) == 'DESC' ? 'DESC' : 'ASC';
                        $orderByArr[] = "`$k` $v";
                    }
                }
                $orderBy = implode(',', $orderByArr);
            }
            return ' ORDER BY ' . $orderBy;
        }
        return '';
    }

    /**
     * 更新指定记录
     * @param <type> $pkValue
     * @param <type> $data
     * @return <type>
     */
    protected function _update($pkValue, $data) {

        if (!$data || !is_array($data)) {
            throw new DAO_Exception('Update data can not be empty', 411);
        }

        // 编码
        $data = $this->_encodeData($data);

        $fields = $wheres = $params = array();
        foreach ($data as $key => $value) {
            $fields[] = $key;
            if (is_array($value)) {
                $wheres[] = "`$key` = `$key` $value[0] ?";
                $params[] = $value[1];
            } else {
                $wheres[] = "`$key` = ?";
                $params[] = $value;
            }
        }

        // 检查字段
        $this->_checkFields($fields);

        $pkCondition = $this->_makePkCondition($pkValue);
        $sql = 'UPDATE `' . $this->_table . '` SET ' . implode(',', $wheres) . ' WHERE ' . implode(' AND ', $pkCondition['condition']);
        $params = array_merge($params, $pkCondition['param']);
        try {
            $result = $this->_db->execute($sql, $params);
            return $result ? true : false;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 根据条件更新
     * @param <type> $conditions
     */
    protected function _updateByCondition($conditions, $data) {

        if (!$data || !is_array($data)) {
            throw new DAO_Exception('Update data can not be empty', 411);
        }

        // 编码
        $data = $this->_encodeData($data);

        $fields = $wheres = $params = array();
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $wheres[] = "`$key` = ?";
            $params[] = $value;
        }

        // 检查字段
        $this->_checkFields($fields);

        $conditions = $this->_makeCondition($conditions);
        $sql = 'UPDATE `' . $this->_table . '` SET ' . implode(',', $wheres) . ' WHERE ' . $conditions['condition'];
        $params = array_merge($params, $conditions['param']);
        try {
            $result = $this->_db->execute($sql, $params);
            return $result ? true : false;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 删除指定记录
     * @param <type> $pkValue
     * @return <type>
     */
    protected function _delete($pkValue) {

        $pkCondition = $this->_makePkCondition($pkValue);
        $sql = 'DELETE FROM `' . $this->_table . '` WHERE ' . implode(' AND ', $pkCondition['condition']);
        $params = $pkCondition['param'];
        try {
            $result = $this->_db->execute($sql, $params);
            return $result ? true : false;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 根据条件删除
     * @param <type> $conditions
     */
    protected function _deleteByCondition($conditions) {

        $conditions = $this->_makeCondition($conditions);
        $sql = 'DELETE FROM `' . $this->_table . '` WHERE ' . $conditions['condition'];
        $params = $conditions['param'];
        try {
            $result = $this->_db->execute($sql, $params);
            return $result ? true : false;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 根据主键获取对于记录
     * @param <type> $pkValue
     * @param <type> $fields
     * @return <type>
     */
    protected function _get($pkValue, $fields = null) {

        $pkCondition = $this->_makePkCondition($pkValue);
        $sql = 'SELECT ' . $this->_buildFields($fields) . ' FROM `' . $this->_table . '` WHERE ' . implode(' AND ', $pkCondition['condition']);
        $params = $pkCondition['param'];
        try {
            $data = $this->_db->fetchRow($sql, $params);
            return $data ? $this->_decodeData($data) : array();
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 根据主键获取信息
     * @param <type> $pkValues
     * @param <type> $fields
     * @return <type>
     */
    protected function _listByPks($pkValues, $fields = null) {

        if (!$pkValues || !is_array($pkValues)) {
            throw new DAO_Exception('Pk values does not given');
        }

        // 拼接 sql， 区分主键只有一个和多个情况
        $params = array();
        $sql = 'SELECT ' . $this->_buildFields($fields) . ' FROM `' . $this->_table . '` WHERE ';
        if (count($this->_pk) == 1) {
            $sql .= '`' . $this->_pk[0] . '` IN (' . implode(',', array_fill(0, count($pkValues), '?')) . ')';
            $params = array_values($pkValues);
        } else {
            $conditions = array();
            foreach ($pkValues as $pkValue) {
                $pkCondition = $this->_makePkCondition($pkValue);
                $conditions[] = '(' . implode(' AND ', $pkCondition['condition']) . ')';
                $params = array_merge($params, $pkCondition['param']);
            }
            $sql .= implode(' OR ', $conditions);
        }

        try {
            $rows = $this->_db->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $key => $row) {
                    $rows[$key] = $this->_decodeData($row);
                }
            }

            return $rows;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 按指定条件查询
     * @param <type> $conditions
     *  + array([field] => [value])
     * @param <type> $fields
     * @param <type> $orderBy
     * @param <type> $offset
     * @param <type> $limit
     * @return <type>
     */
    protected function _select($conditions = array(), $fields = null, $orderBy = null, $offset = 0, $limit = 1) {

        // 参数检查
        if ($offset < 0 || $limit < 1) {
            throw new DAO_Exception("offset or limit is illegal: $offset, $limit");
        }

        // 拼 sql
        $conditions = $this->_makeCondition($conditions);
        $sql = 'SELECT ' . $this->_buildFields($fields) . ' FROM `' . $this->_table . '`' . ' WHERE ' . $conditions['condition'];
        $params = $conditions['param'];

        $sql .= $this->_makeOrderBy($orderBy);

        if ($limit > 0) {
            $sql .= ' LIMIT ' . intval($offset) . ',' .intval($limit);
        }

        try {
            $rows = $this->_db->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $key => $row) {
                    $rows[$key] = $this->_decodeData($row);
                }
            }

            return $rows;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 计算数据行数
     * @param <type> $conditions
     *  + array([field] => [value])
     * @return <type>
     */
    protected function _count($conditions = array()) {

        $conditions = $this->_makeCondition($conditions);
        $sql = 'SELECT COUNT(*) FROM `' . $this->_table . '`' . '  WHERE ' . $conditions['condition'];
        $params = $conditions['param'];

        try {
            return $this->_db->fetchOne($sql, $params);
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 开始事物处理
     */
    public function beginTransaction() {

        try {
            $this->_db->begin();
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 事物回滚
     */
    public function rollBack() {

        try {
            $this->_db->rollBack();
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }

    /**
     * 提交事物
     */
    public function commit() {

        try {
            $this->_db->commit();
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }
}