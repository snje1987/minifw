<?php

/*
 * Copyright (C) 2013 Yang Ming <yangming0116@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @filename Mysqli.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-4-8 13:35:37
 * @version 1.0
 * @Description 定义一些数据表的公共操作
 */

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as Minifw;

/**
 * 定义数据表的基本操作
 */
abstract class Table {

    /**
     * @var string 数据表的名称
     */
    const TBNAME = '';
    const ALLOW = ['edit', 'add', 'del'];

    protected static $_instance = [];

    /**
     * @var Minifw\DB 数据库的实例
     */
    protected $_db;

    /**
     * 获取数据表实例
     *
     * @param array args 实例参数
     * @return static 数据表实例
     */
    public static function get($args = []) {
        $id = '';
        if (!empty($args)) {
            $id = strval($args['id']);
        }
        if (!isset(self::$_instance[$id])) {
            self::$_instance[$id] = [];
        }
        if (!isset(self::$_instance[$id][static::class])) {
            self::$_instance[$id][static::class] = new static($args);
        }
        return self::$_instance[$id][static::class];
    }

    /**
     * 启用事务处理，执行指定的方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function sync_call($post, $call, $die = true) {
        $call = strval($call);
        if (in_array($call, static::ALLOW)) {
            $this->_db->begin();
            if (Common::json_call($post, [$this, $call], false)) {
                $this->_db->commit();
            } else {
                $this->_db->rollback();
            }
        }
        if ($die) {
            die(0);
        }
    }

    /**
     * 使用Ajax方式调用指定方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function json_call($post, $call, $die = true) {
        $call = strval($call);
        if (in_array($call, static::ALLOW)) {
            Common::json_call($post, [$this, $call], $die);
        } else {
            if ($die) {
                die(0);
            }
        }
    }

    /**
     * 根据条件计算数据的条数
     *
     * @param array $condition 计算的条件
     * @return int 数据的数量
     */
    public function count($condition = []) {
        return $this->_db->count(static::TBNAME, $condition);
    }

    /**
     * 在表中插入一条数据
     *
     * @param array $post 要插入的数据
     * @return bool 成功返回true，失败返回false
     */
    public function add($post) {
        $data = $this->_prase($post, 1);
        return $this->_db->insert(static::TBNAME, $data);
    }

    /**
     * 修改表中的一条数据
     *
     * @param array $post 修改的条件和数值
     * @return bool 成功返回true，否则返回false
     */
    public function edit($post) {
        $data = $this->_prase($post, 2);
        $condition = [];
        $condition['id'] = intval($post['id']);
        return $this->_db->update(static::TBNAME, $data, $condition);
    }

    /**
     * 根据id修改表中指定字段的值
     *
     * @param int $id 要修改的数据的id
     * @param string $field 要修改的字段
     * @param mixed $value 要修改成的值
     * @return bool 成功返回true，否则返回false
     */
    public function set_field($id, $field, $value) {
        $condition = [];
        $condition['id'] = intval($id);
        $data = [];
        $data[strval($field)] = $value;
        return $this->_db->update(static::TBNAME, $data, $condition);
    }

    /**
     * 删除指定id的数据
     *
     * @param int $args 要删除数据的信息
     * @return bool 成功返回ture，否则返回fasle
     */
    public function del($args) {
        $id = intval($args[0]);
        $condition = [
            'id' => $id
        ];
        return $this->_db->delete(static::TBNAME, $condition);
    }

    /**
     * 根据id获取指定的数据
     *
     * @param int $id 要获取的数据的id
     * @return array 具有指定id的数据
     */
    public function get_by_id($id) {
        $condition = [];
        $condition['id'] = intval($id);
        return $this->_db->one_query(static::TBNAME, $condition);
    }

    /**
     * 根据条件获取一条数据
     *
     * @param array $condition 查询的条件
     * @param array $field 查询的字段
     * @return array 要查询的数据
     */
    public function get_one($condition, $field = []) {
        return $this->_db->one_query(static::TBNAME, $condition, $field);
    }

    /**
     * 根据指定字段的值获取一条数据
     *
     * @param string $field 指定的字段
     * @param string $value 指定的值
     * @return array 查询的结果
     */
    public function get_by_field($field, $value) {
        $field = strval($field);
        $value = strval($value);
        $condition = [];
        $condition[$field] = $value;
        return $this->_db->one_query(static::TBNAME, $condition);
    }

    /**
     * 根据指定字段的值获取符合条件的数据
     *
     * @param string $field 指定的字段
     * @param string $value 指定的值
     * @return array 查询的结果
     */
    public function gets_by_field($field, $value) {
        $field = strval($field);
        $value = strval($value);
        $condition = [];
        $condition[$field] = $value;
        return $this->_db->limit_query(static::TBNAME, $condition);
    }

    /**
     * 根据条件获取符合条件的数据
     *
     * @param array $condition 查询的条件
     * @param array $field 查询的字段
     * @return array 查询的结果
     */
    public function gets_by_condition($condition = [], $field = []) {
        return $this->_db->limit_query(static::TBNAME, $condition, $field);
    }

    /**
     * 根据sql语句获取符合条件的的数据
     *
     * @param string $sql sql语句
     * @return array 查询的结果
     */
    public function gets_by_query($sql) {
        return $this->_db->get_query($sql);
    }

    /**
     * 执行sql语句
     *
     * @param string $sql sql语句
     * @return mixed 查询的结果
     */
    public function query($sql) {
        return $this->_db->query($sql);
    }

    /**
     * 开启事务
     */
    public function begin() {
        $this->_db->begin();
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->_db->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->_db->rollback();
    }

    public function drop() {
        $this->_db->query('drop table if exists `' . static::TBNAME . '`');
    }

    /**
     * 创建该数据表
     */
    abstract public function create();

    /*     * ******************************************************* */

    /**
     * 私有构造函数
     */
    protected function __construct($args = []) {
        $this->_db = DB::get($args);
    }

    /**
     * 虚函数，用于处理用户的输入，生成执行插入和修改的数据值
     */
    abstract protected function _prase($post, $type);
}
