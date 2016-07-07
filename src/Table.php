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
    const ENGINE = 'InnoDB';
    const CHARSET = 'utf8';

    /**
     * @var array 表中的列定义
     * 结构：
     * [
     *     [
     *         'name' => 'test',
     *         'type' => 'varchar',
     *         'len' => '255',
     *         'attr' => '',
     *         'default' => '',
     *         'extra' => 'AUTO_INCREMENT',
     *         'comment' => '测试'
     *     ],
     *     ...
     * ]
     *
     */
    const FIELDS = [];

    /**
     * @var array 表中的索引定义
     * 结构：
     * [
     *     [
     *         'type' => 'primary',
     *         'name' => '',
     *         'fields' => ['id'],
     *     ],
     *     ...
     * ]
     *
     */
    const INDEXS = [];

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
     * 对比指定目录中所有的数据表
     *
     * @param string $ns 目录对应的名空间
     * @param string $path 目录的绝对路径
     * @param bool $top 是否为第一级调用
     */
    public static function diff_all($ns = '', $path = '', $top = true) {
        if ($path == '' || !is_dir($path)) {
            return;
        }
        if ($top) {
            echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>数据库结构检查</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><style>*{padding:0;margin:0;}table{width:80%;margin:20px auto;border-collapse:collapse;border:1px solid black;font-size:12px;line-height:16px;}th{border:1px solid black;padding:4px;background-color:#DDDDDD;}td{border:1px solid black;padding:4px;}.red{color:red;}.green{color:green;}.blue{color:blue;}.center{text-align:center;}</style></head><body>';

            echo '<table><tr><th>数据表</th><th>类型</th><th>变化/转换SQL</th></tr>';
        }
        try {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . '/' . $file)) {
                    self::diff_all($ns . '\\' . $file, $path . '/' . $file, false);
                } else {
                    if (substr($file, -4, 4) !== '.php') {
                        continue;
                    }
                    $classname = $ns . '\\' . substr($file, 0, strlen($file) - 4);
                    if (class_exists($classname)) {
                        $obj = new $classname();
                        if ($obj instanceof Table) {
                            $obj->table_diff();
                        }
                    }
                }
            }
            closedir($dir);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        if ($top) {
            echo '</table></body></html>';
            die();
        }
    }

    /**
     * 启用事务处理，执行指定的方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function sync_call($post, $call, $die = true) {
        $call = strval($call);
        $this->_db->begin();
        if (Common::json_call($post, [$this, $call], false)) {
            $this->_db->commit();
        } else {
            $this->_db->rollback();
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
        Common::json_call($post, [$this, $call], $die);
        if ($die) {
            die(0);
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
     * @param mixed $args 要删除数据的信息
     * @return bool 成功返回ture，否则返回fasle
     */
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

    /**
     * 删除数据表
     *
     * @return bool
     */
    public function drop() {
        return $this->_db->query('drop table if exists `' . static::TBNAME . '`');
    }

    /**
     * 根据列定义数组生成定义SQL
     *
     * @param array $attr 定义数组
     * @return string 定义SQL
     */
    public static function field_sql($attr) {
        $tmp = '';
        switch ($attr['type']) {
            case 'text':
                $tmp = '`' . $attr['name'] . '` text';
                if (!isset($attr['null']) || $attr['null'] === 'NO') {
                    $tmp .= ' NOT NULL';
                }
                break;
            default :
                $tmp = '`' . $attr['name'] . '` ' . $attr['type'];
                if (!isset($attr['null']) || $attr['null'] === 'NO') {
                    $tmp .= ' NOT NULL';
                }
                if (isset($attr['extra']) && $attr['extra'] !== null && $attr['extra'] !== '') {
                    $tmp .= ' ' . $attr['extra'];
                }
                if (isset($attr['default']) && $attr['default'] !== null) {
                    $tmp .= ' DEFAULT "' . $attr['default'] . '"';
                }
                break;
        }
        if (isset($attr['comment']) && $attr['comment'] !== null) {
            $tmp .= ' COMMENT "' . $attr['comment'] . '"';
        }
        return $tmp;
    }

    /**
     * 根据索引定义数组生成定义SQL
     *
     * @param array $attr 定义数组
     * @return string 定义SQL
     */
    public static function index_sql($attr) {
        $tmp = '';
        switch ($attr['type']) {
            case 'PRIMARY':
                $tmp = 'PRIMARY KEY (`' . implode('`,`', $attr['fields']) . '`)';
                break;
            default :
                $tmp = $attr['type'] . ' `' . $attr['name'] . '` (`' . implode('`,`', $attr['fields']) . '`)';
                break;
        }
        return $tmp;
    }

    /**
     * 创建该数据表
     *
     * @param bool $recreate 如果存在是否重建
     * @return bool
     * @throws Exception
     */
    public function create($recreate = false) {
        if ($recreate == true && !$this->drop()) {
            throw new Exception('重建失败');
        }
        $sql = 'CREATE TABLE IF NOT EXISTS `' . static::TBNAME . '` (';
        $arr = [];
        foreach (static::FIELDS as $v) {
            $arr[] = self::field_sql($v);
        }

        foreach (static::INDEXS as $v) {
            $arr[] = self::index_sql($v);
        }

        $sql .= implode(',', $arr);
        $sql .= ') ENGINE=' . static::ENGINE . ' DEFAULT CHARSET=' . static::CHARSET;
        if (!$this->_db->query($sql)) {
            throw new Exception($this->_db->last_error());
        }
        return true;
    }

    /**
     * 对比数据表的定义以及实际的数据库结构
     */
    public function table_diff() {
        $sql = 'show full fields from `' . static::TBNAME . '`';
        $data = $this->_db->get_query($sql);
        $k = 0;
        foreach ($data as $k => $v) {
            $this->field_diff($v, $k);
        }
        for ($k ++; $k < count(static::FIELDS); $k ++) {
            $right = static::FIELDS[$k];
            $right_sql = self::field_sql($right);
            echo '<tr><th>' . static::TBNAME . '</th><td class="center">列</td><td>';
            echo '<p class="green">+&nbsp;' . $right_sql . '</p>';
            echo '<p class="blue">=&nbsp;ALTER TABLE `' . static::TBNAME . '` ADD ' . $right_sql . ';</p>';
            echo '</td></tr>';
        }
    }

    /**
     * 对比数据表中一列的定义以及实际的数据库中的列
     *
     * @param array $dbattr 列在实际数据库中的定义
     * @param int $index 列的索引号，从0开始
     */
    public function field_diff($dbattr, $index) {
        $left = [];

        $hash = [
            'Field' => 'name',
            'Type' => 'type',
            'Null' => 'null',
            'Extra' => 'extra',
            'Default' => 'default',
            'Comment' => 'comment',
        ];

        foreach ($hash as $k => $v) {
            $left[$v] = $dbattr[$k];
        }

        $left_sql = self::field_sql($left);
        $right_sql = '';

        if ($index < count(static::FIELDS)) {
            $right = static::FIELDS[$index];
            $right_sql = self::field_sql($right);
        }

        if ($left_sql != $right_sql) {
            echo '<tr><th>' . static::TBNAME . '</th><td class="center">列</td><td>';
            echo '<p class="red">-&nbsp;' . $left_sql . '</p>';
            if ($right_sql != '') {
                echo '<p class="green">+&nbsp;' . $right_sql . '</p>';
                echo '<p class="blue">=&nbsp;ALTER TABLE `' . static::TBNAME . '` CHANGE `' . $left['name'] . '` ' . $right_sql . ';</p>';
            } else {
                echo '<p class="blue">=&nbsp;ALTER TABLE `' . static::TBNAME . '` DROP `' . $left['name'] . '`;</p>';
            }
            echo '</td></tr>';
        }
    }

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
