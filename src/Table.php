<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;

abstract class Table {

    public static $tbname = '';
    public static $status = array();
    public static $field = array();
    public static $index = array();

    protected static $_instance = array();

    /**
     * 获取实例
     * @return static 实例
     */
    public static function get($args = array(), $id = '') {
        $class = get_called_class();
        if (!isset(static::$_instance[$class])) {
            static::$_instance[$class] = array();
        }
        if (!isset(static::$_instance[$class][$id])) {
            static::$_instance[$class][$id] = new static($args);
        }
        return static::$_instance[$class][$id];
    }

    /**
     * @var FW\DB
     */
    protected $db;

    public function get_db() {
        return $this->db;
    }

    public function count($condition = array()) {
        return $this->db->count(static::$tbname, $condition);
    }

    public function add($post) {
        $data = $this->_prase($post, 1);
        return $this->db->insert(static::$tbname, $data);
    }

    public function edit($post) {
        $data = $this->_prase($post, 2);
        $condition = array();
        $condition['id'] = intval($post['id']);
        return $this->db->update(static::$tbname, $data, $condition);
    }

    public function set_field($id, $field, $value) {
        $condition = array();
        $condition['id'] = intval($id);
        $data = array();
        $data[strval($field)] = $value;
        return $this->db->update(static::$tbname, $data, $condition);
    }

    public function change_field($id, $field, $value) {
        $sql = 'update `' . static::$tbname . '` set `' . $field . '` = ' . $value . ' where `id`="' . $id . '"';
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

        $condition = array(
            'id' => $id
        );
        return $this->db->delete(static::$tbname, $condition);
    }

    public function get_by_id($id, $field = array(), $lock = false) {
        $condition = array();
        $condition['id'] = intval($id);
        return $this->db->one_query(static::$tbname, $condition, $field, $lock);
    }

    public function get_one($condition, $field = array(), $lock = false) {
        return $this->db->one_query(static::$tbname, $condition, $field, $lock);
    }

    public function get_by_field($name, $value, $field = array(), $lock = false) {
        $name = strval($name);
        $value = strval($value);
        $condition = array();
        $condition[$name] = $value;
        return $this->db->one_query(static::$tbname, $condition, $field, $lock);
    }

    public function gets_by_field($field, $value) {
        $field = strval($field);
        $value = strval($value);
        $condition = array();
        $condition[$field] = $value;
        return $this->db->limit_query(static::$tbname, $condition);
    }

    public function gets_by_condition($condition = array(), $field = array()) {
        return $this->db->limit_query(static::$tbname, $condition, $field);
    }

    public function drop() {
        $sql = $this->db->drop_table_sql(static::$tbname);
        return $this->db->query($sql);
    }

    public function create($recreate = false) {
        if ($recreate == true && !$this->drop()) {
            throw new Exception('failed to drop table');
        }
        $sql = $this->db->create_table_sql(
                static::$tbname
                , static::$status
                , static::$field
                , static::$index);
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
        $diff = array();
        $status = null;
        $field = null;
        $index = null;
        try {
            $status = $this->db->get_table_status(static::$tbname);
            $field = $this->db->get_table_field(static::$tbname);
            $index = $this->db->get_table_index(static::$tbname);
        } catch (Exception $ex) {
            $sql_display = $this->db->create_table_sql(
                    static::$tbname
                    , static::$status
                    , static::$field
                    , static::$index
                    , "\n+ ");
            $sql_exec = $this->db->create_table_sql(
                    static::$tbname
                    , static::$status
                    , static::$field
                    , static::$index
                    , "\n");
            $diff[] = array(
                'diff' => '+' . $sql_display,
                'trans' => $sql_exec . ';',
            );
            $init_sql = $this->init_table_sql();
            if ($init_sql !== '') {
                $diff[] = array(
                    'diff' => '+' . $init_sql,
                    'trans' => $init_sql . ';',
                );
            }
            return $diff;
        }
        $diff = $this->db->get_status_diff(static::$tbname, $status, static::$status);
        list($fdiff, $removed) = $this->db->get_field_diff(static::$tbname, $field, static::$field);
        if (!empty($fdiff)) {
            $diff = array_merge($diff, $fdiff);
        }
        $idiff = $this->db->get_index_diff(static::$tbname, $index, static::$index, $removed);
        if (!empty($idiff)) {
            $diff = array_merge($diff, $idiff);
        }
        return $diff;
    }

    ///////////////////////////////////////////////////

    protected function __construct($args = array()) {
        $this->db = DB::get_default($args);
    }

    abstract protected function _prase($post, $type);
}
