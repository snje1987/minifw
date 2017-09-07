<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;

abstract class Table {

    const TBNAME = '';
    const STATUS = [];
    const FIELD = [];
    const INDEX = [];

    protected static $_instance = [];

    /**
     * 获取实例
     * @return static 实例
     */
    public static function get($args = array(), $id = '') {
        if (!isset(static::$_instance[static::class])) {
            static::$_instance[static::class] = [];
        }
        if (!isset(static::$_instance[static::class][$id])) {
            static::$_instance[static::class][$id] = new static($args);
        }
        return static::$_instance[static::class][$id];
    }

    /**
     * @var FW\DB
     */
    protected $db;

    public function get_db() {
        return $this->db;
    }

    public function count($condition = []) {
        return $this->db->count(static::TBNAME, $condition);
    }

    public function add($post) {
        $data = $this->_prase($post, 1);
        return $this->db->insert(static::TBNAME, $data);
    }

    public function edit($post) {
        $data = $this->_prase($post, 2);
        $condition = [];
        $condition['id'] = intval($post['id']);
        return $this->db->update(static::TBNAME, $data, $condition);
    }

    public function set_field($id, $field, $value) {
        $condition = [];
        $condition['id'] = intval($id);
        $data = [];
        $data[strval($field)] = $value;
        return $this->db->update(static::TBNAME, $data, $condition);
    }

    public function change_field($id, $field, $value) {
        $sql = 'update `' . static::TBNAME . '` set `' . $field . '` = ' . $value . ' where `id`="' . $id . '"';
        return $this->db->query($sql);
    }

    public function del($args) {
        $id = 0;
        if (is_array($args)) {
            $id = intval($args[0]);
        } else {
            $id = intval($args);
        }

        if ($id == 0) {
            return false;
        }

        $condition = [
            'id' => $id
        ];
        return $this->db->delete(static::TBNAME, $condition);
    }

    public function get_by_id($id, $field = [], $lock = false) {
        $condition = [];
        $condition['id'] = intval($id);
        return $this->db->one_query(static::TBNAME, $condition, $field, $lock);
    }

    public function get_one($condition, $field = [], $lock = false) {
        return $this->db->one_query(static::TBNAME, $condition, $field, $lock);
    }

    public function get_by_field($name, $value, $field = [], $lock = false) {
        $name = strval($name);
        $value = strval($value);
        $condition = [];
        $condition[$name] = $value;
        return $this->db->one_query(static::TBNAME, $condition, $field, $lock);
    }

    public function gets_by_field($field, $value) {
        $field = strval($field);
        $value = strval($value);
        $condition = [];
        $condition[$field] = $value;
        return $this->db->limit_query(static::TBNAME, $condition);
    }

    public function gets_by_condition($condition = [], $field = []) {
        return $this->db->limit_query(static::TBNAME, $condition, $field);
    }

    public function gets_by_query($sql) {
        return $this->db->get_query($sql);
    }

    public function query($sql) {
        return $this->db->query($sql);
    }

    public function drop() {
        $sql = $this->db->drop_table_sql(static::TBNAME);
        return $this->db->query($sql);
    }

    public function create($recreate = false) {
        if ($recreate == true && !$this->drop()) {
            throw new Exception('failed to drop table');
        }
        $sql = $this->db->create_table_sql(
                static::TBNAME
                , static::STATUS
                , static::FIELD
                , static::INDEX);
        if (!$this->db->query($sql)) {
            throw new Exception($this->db->last_error());
        }
        if (!$this->init_table()) {
            throw new Exception($this->db->last_error());
        }
        return true;
    }

    public function init_table() {
        $sql = $this->init_table_sql();
        if ($sql !== '') {
            return $this->db->query($sql);
        }
        return true;
    }

    public function init_table_sql() {
        return '';
    }

    public function table_diff() {
        $diff = [];
        $status = null;
        $field = null;
        $index = null;
        try {
            $status = $this->db->get_table_status(static::TBNAME);
            $field = $this->db->get_table_field(static::TBNAME);
            $index = $this->db->get_table_index(static::TBNAME);
        } catch (Exception $ex) {
            $sql_display = $this->db->create_table_sql(
                    static::TBNAME
                    , static::STATUS
                    , static::FIELD
                    , static::INDEX
                    , "\n+ ");
            $sql_exec = $this->db->create_table_sql(
                    static::TBNAME
                    , static::STATUS
                    , static::FIELD
                    , static::INDEX
                    , "\n");
            $diff[] = [
                'diff' => '+' . $sql_display,
                'trans' => $sql_exec . ';',
            ];
            $init_sql = $this->init_table_sql();
            if ($init_sql !== '') {
                $diff[] = [
                    'diff' => '+' . $init_sql,
                    'trans' => $init_sql . ';',
                ];
            }
            return $diff;
        }
        $diff = $this->db->get_status_diff(static::TBNAME, $status, static::STATUS);
        list($fdiff, $removed) = $this->db->get_field_diff(static::TBNAME, $field, static::FIELD);
        if (!empty($fdiff)) {
            $diff = array_merge($diff, $fdiff);
        }
        $idiff = $this->db->get_index_diff(static::TBNAME, $index, static::INDEX, $removed);
        if (!empty($idiff)) {
            $diff = array_merge($diff, $idiff);
        }
        return $diff;
    }

    ///////////////////////////////////////////////////

    protected function __construct($args = []) {
        $this->db = DB::get_default($args);
    }

    abstract protected function _prase($post, $type);
}
