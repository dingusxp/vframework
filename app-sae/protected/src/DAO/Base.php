<?php
/**
 * DAO Base 通用基类
 */

class DAO_Base extends DAO_Mysql {

    /**
     * 添加数据
     * @param type $data
     * @return type
     */
    public function add($data) {

        return $this->_insert($data);
    }

    /**
     * 获取指定数据
     * @param type $key
     * @param type $fields
     * @return type
     */
    public function get($key, $fields = array()) {

        return $this->_get($key, $fields);
    }

    /**
     * 批量获取指定数据
     * @param type $keys
     * @param type $fields
     * @return type
     */
    public function listByPKs($keys, $fields = array()) {

        return $this->_listByPks($keys, $fields);
    }

    /**
     * 更新指定数据
     * @param type $key
     * @param type $data
     * @return type
     */
    public function update($key, $data) {

        return $this->_update($key, $data);
    }

    /**
     * 删除指定数据
     * @param type $key
     * @return type
     */
    public function delete($key) {

        return $this->_delete($key);
    }

    /**
     * 列出全部数据
     * @param type $fields
     * @param type $orderBy
     * @param type $offset
     * @param type $limit
     * @return type
     */
    public function listAll($fields = array(), $orderBy = array(), $offset = 0, $limit = 1) {

        return $this->_select(array(), $fields, $orderBy, $offset, $limit);
    }

    /**
     * 返回数据总数
     * @return type
     */
    public function countAll() {

        return $this->_count();
    }

    /**
     * 插入新数据
     * @param array $fields
     * @param array $rows
     * @return boolean
     */
    protected function _insertBatch($fields, $rows) {

        $items = $params = array();
        foreach ($rows as $data) {

            // 设置默认值
            $data = $this->_setDefault($data);

            // 编码
            $data = $this->_encodeData($data);

            // 拼接 sql
            $marks = array();
            foreach ($data as $value) {
                $marks[] = '?';
                $params[] = $value;
            }
            $items[] = '(' . implode(',', $marks) . ')';
        }

        // 检查字段
        $this->_checkFields($fields);
        $this->_checkRequired($fields);

        $sql = 'INSERT INTO `' . $this->_table . '`(`' . implode('`, `', $fields) . '`) VALUES '.implode(',', $items);
        try {
            $this->_db->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new DAO_Exception($e);
        }
    }
}