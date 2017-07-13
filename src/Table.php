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

    /**
     * @var string 存储引擎
     */
    const ENGINE = 'InnoDB';

    /**
     * @var string 默认字符集
     */
    const CHARSET = 'utf8';

    /**
     * @var string 表注释
     */
    const COMMENT = '';

    /**
     * @var array 表中的列定义
     * 结构：
     * [
     *     'test' => [
     *         'type' => 'varchar(200)',
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
     *
     * 结构：
     * [
     *     'PRIMARY' => [
     *         'unique' => true,
     *         'fields' => ['id'],
     *     ]
     *     ...
     * ]
     *
     */
    const INDEXS = [];

    use Minifw\Traits\PublicInstance;

    /**
     * @var Minifw\DB 数据库的实例
     */
    protected $_db;
    private static $_diff = [];

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
            self::$_diff = [];
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
                            $ndiff = $obj->table_diff();
                            if (empty($ndiff)) {
                                continue;
                            }
                            self::$_diff = array_merge(self::$_diff, $ndiff);
                        }
                    }
                }
            }
            closedir($dir);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        if ($top) {
            header("Content-Type:text/plain;charset=utf-8");

            $otable = '';
            $trans = [];
            foreach (self::$_diff as $v) {
                if ($otable == '' || $otable != $v['table']) {
                    echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
                    $otable = $v['table'];
                    echo $otable . "\n\n";
                }
                echo $v['diff'] . "\n";
                $trans[] = $v['trans'];
            }
            echo "\n\n================================================================\n\n";
            echo implode("\n", $trans);
            die();
        }
    }

    /**
     * 启用事务处理，执行指定的方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function sync_call($post, $call, $mode = Common::JSON_CALL_DIE) {
        $call = strval($call);
        $this->_db->begin();
        $ret = Common::json_call($post, [$this, $call], Common::JSON_CALL_RETURN);
        if ($ret['succeed'] == true) {
            $this->_db->commit();
        } else {
            $this->_db->rollback();
        }
        if ($mode == Common::JSON_CALL_REDIRECT) {
            if ($ret['returl'] != '') {
                Server::redirect($ret['returl']);
            } else {
                Server::redirect('/');
            }
        } elseif ($mode == Common::JSON_CALL_DIE) {
            // @codeCoverageIgnoreStart
            die(\json_encode($ret, JSON_UNESCAPED_UNICODE));
            // @codeCoverageIgnoreEnd
        } else {
            return $ret;
        }
    }

    /**
     * 使用Ajax方式调用指定方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function json_call($post, $call, $mode = Common::JSON_CALL_DIE) {
        $call = strval($call);
        return Common::json_call($post, [$this, $call], $mode);
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

    public function change_field($id, $field, $value) {
        $sql = 'update `' . static::TBNAME . '` set `' . $field . '` = ' . $value . ' where `id`="' . $id . '"';
        return $this->_db->query($sql);
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
     * @param string $name 名称
     * @param array $attr 定义数组
     * @return string 定义SQL
     */
    public static function field_sql($name, $attr) {
        $tmp = '';
        switch ($attr['type']) {
            case 'text':
                $tmp = '`' . $name . '` text';
                if (!isset($attr['null']) || $attr['null'] === 'NO') {
                    $tmp .= ' NOT NULL';
                }
                break;
            default :
                $tmp = '`' . $name . '` ' . $attr['type'];
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
     * @param string $name 索引名称
     * @param array $attr 定义数组
     * @param bool $in_create 用于表创建语句
     * @return string 定义SQL
     */
    public static function index_sql($name, $attr, $in_create = true) {
        $tmp = '';
        switch ($name) {
            case 'PRIMARY':
                $tmp = 'PRIMARY KEY (`' . implode('`,`', $attr['fields']) . '`)';
                break;
            default :
                if ($in_create) {
                    if (isset($attr['unique']) && $attr['unique'] === true) {
                        $tmp = 'UNIQUE ';
                    } else if (isset($attr['fulltext']) && $attr['fulltext'] === true) {
                        $tmp = 'FULLTEXT ';
                    }
                    $tmp .= 'KEY ';
                } else {
                    if (isset($attr['unique']) && $attr['unique'] === true) {
                        $tmp = 'UNIQUE ';
                    } elseif (isset($attr['fulltext']) && $attr['fulltext'] === true) {
                        $tmp = 'FULLTEXT ';
                    } else {
                        $tmp = 'INDEX ';
                    }
                }
                $tmp .= '`' . $name . '` (`' . implode('`,`', $attr['fields']) . '`)';
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
        $sql = $this->create_sql();
        if (!$this->_db->query($sql)) {
            throw new Exception($this->_db->last_error());
        }
        if (!$this->init_table()) {
            throw new Exception($this->_db->last_error());
        }
        return true;
    }

    /**
     * 生成建立表结构的sql语句
     *
     * @param string $dim 分隔符
     * @return string
     */
    public function create_sql($dim = '') {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . static::TBNAME . "` (" . $dim;
        $arr = [];
        foreach (static::FIELDS as $k => $v) {
            $arr[] = self::field_sql($k, $v);
        }

        foreach (static::INDEXS as $k => $v) {
            $arr[] = self::index_sql($k, $v);
        }

        $sql .= implode("," . $dim, $arr) . $dim;
        $sql .= ') ENGINE=' . static::ENGINE . ' DEFAULT CHARSET=' . static::CHARSET;
        if (static::COMMENT != '') {
            $sql .= ' COMMENT="' . static::COMMENT . '"';
        }
        return $sql;
    }

    public function init_table() {
        $sql = $this->init_table_sql();
        if ($sql !== '') {
            return $this->_db->query($sql);
        }
        return true;
    }

    public function init_table_sql() {
        return '';
    }

    /**
     * 对比数据表的定义以及实际的数据库结构
     *
     * @return array 实际数据表与定义的差异
     */
    public function table_diff() {
        $diff = [];
        try {
            $diff = $this->status_diff();
        } catch (Exception $ex) {
            $sql = $this->create_sql("\n+ ");
            $sql1 = $this->create_sql("\n");
            $diff[] = [
                'table' => static::TBNAME,
                'diff' => '+ ' . $sql,
                'trans' => $sql1 . ';',
            ];
            $init_sql = $this->init_table_sql();
            if ($init_sql !== '') {
                $diff[] = [
                    'table' => static::TBNAME,
                    'diff' => '+ ' . $init_sql,
                    'trans' => $init_sql . ';',
                ];
            }
            return $diff;
        }
        $fdiff = $this->fields_diff();
        if (!empty($fdiff)) {
            $diff = array_merge($diff, $fdiff);
        }
        $idiff = $this->index_diff();
        if (!empty($idiff)) {
            $diff = array_merge($diff, $idiff);
        }

        return $diff;
    }

    /**
     * 获取表属性的差异，表不存在时抛出异常
     *
     * @return array 表属性的差异
     * @throws Exception
     */
    public function status_diff() {
        $sql = 'show table status like "' . static::TBNAME . '"';
        $data = $this->_db->get_query($sql);
        if (count($data) !== 1) {
            throw new Exception('数据表不存在');
        }
        $data = $data[0];
        $diff = [];
        if ($data['Engine'] != static::ENGINE) {
            $diff[] = [
                'table' => static::TBNAME,
                'diff' => '- Engine=' . $data['Engine'] . "\n" . '+ Engine=' . static::ENGINE,
                'trans' => 'ALTER TABLE `' . static::TBNAME . '` ENGINE=' . static::ENGINE . ';',
            ];
        }
        if ($data['Comment'] != static::COMMENT) {
            $diff[] = [
                'table' => static::TBNAME,
                'diff' => '- Comment="' . $data['Comment'] . "\"\n" . '+ Comment="' . static::COMMENT . '"',
                'trans' => 'ALTER TABLE `' . static::TBNAME . '` COMMENT="' . static::COMMENT . '";',
            ];
        }
        return $diff;
    }

    /**
     * 在移动列之后修改列号
     *
     * @param array& $fields 列数据
     * @param int $from 起始列号，包含
     * @param int $to 终止列号，不包含
     */
    private static function move_no(&$fields, $from, $to = -1) {
        foreach ($fields as $k => $v) {
            if ($v['no'] >= $from && ($to == -1 || $v['no'] < $to)) {
                $fields[$k]['no'] ++;
            }
        }
    }

    /**
     * 获取表列的差异
     *
     * @return array 表列的差异
     */
    public function fields_diff() {
        $fields = $this->get_table_field();
        $diff = [];
        $tail = ' first';
        $i = 0;
        foreach (static::FIELDS as $k => $v) {
            $right_sql = self::field_sql($k, $v);
            if (!isset($fields[$k])) {
                $diff[] = [
                    'table' => static::TBNAME,
                    'diff' => '+[' . $i . '] ' . $right_sql,
                    'trans' => 'ALTER TABLE `' . static::TBNAME . '` ADD ' . $right_sql . $tail . ';',
                ];
                self::move_no($fields, $i);
            } else {
                $left_sql = self::field_sql($k, $fields[$k]);
                if ($left_sql != $right_sql || $i != $fields[$k]['no']) {
                    $diff[] = [
                        'table' => static::TBNAME,
                        'diff' => '-[' . $fields[$k]['no'] . '] ' . $left_sql . "\n" . '+[' . $i . '] ' . $right_sql,
                        'trans' => 'ALTER TABLE `' . static::TBNAME . '` CHANGE `' . $k . '` ' . $right_sql . $tail . ';',
                    ];
                }
                self::move_no($fields, $i, $fields[$k]['no']);
            }
            $tail = ' after `' . $k . '`';
            $i ++;
        }

        foreach ($fields as $k => $v) {
            if (array_key_exists($k, static::FIELDS)) {
                continue;
            }
            $left_sql = self::field_sql($k, $v);
            $diff[] = [
                'table' => static::TBNAME,
                'diff' => '- ' . $left_sql,
                'trans' => 'ALTER TABLE `' . static::TBNAME . '` DROP `' . $k . '`;',
            ];
        }

        return $diff;
    }

    /**
     * 获取表索引的差异
     *
     * @return array 表索引的差异
     */
    public function index_diff() {
        $diff = [];
        $db_index = $this->get_table_index();

        foreach (static::INDEXS as $k => $v) {
            $right_sql = $this->index_sql($k, $v, false);
            if (!isset($db_index[$k])) {
                $diff[] = [
                    'table' => static::TBNAME,
                    'diff' => '+ ' . $right_sql,
                    'trans' => 'ALTER TABLE `' . static::TBNAME . '` ADD ' . $right_sql . ';',
                ];
                continue;
            }
            $left_sql = $this->index_sql($k, $db_index[$k], false);
            if ($left_sql != $right_sql) {
                $trans = 'ALTER TABLE `' . static::TBNAME . '` DROP';
                if ($k == 'PRIMARY') {
                    $trans .= ' PRIMARY KEY';
                } else {
                    $trans .= ' INDEX `' . $k . '`';
                }
                $trans .= ', ADD ' . $right_sql . ';';
                $diff[] = [
                    'table' => static::TBNAME,
                    'diff' => '- ' . $left_sql . "\n" . '+ ' . $right_sql,
                    'trans' => $trans,
                ];
                continue;
            }
        }

        foreach ($db_index as $k => $v) {
            if (array_key_exists($k, static::INDEXS)) {
                continue;
            }
            $left_sql = $this->index_sql($k, $v, false);
            $trans = 'ALTER TABLE `' . static::TBNAME . '` DROP INDEX `' . $k . '`;';
            if ($k == 'PRIMARY') {
                $trans = 'ALTER TABLE `' . static::TBNAME . '` DROP PRIMARY KEY;';
            }

            $diff[] = [
                'table' => static::TBNAME,
                'diff' => '- ' . $left_sql,
                'trans' => $trans,
            ];
        }

        return $diff;
    }

    /**
     * 获取实际数据表列的定义
     *
     * @return array 实际数据表列的定义
     */
    public function get_table_field() {
        $sql = 'show full fields from `' . static::TBNAME . '`';
        $data = $this->_db->get_query($sql);
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[$v['Field']] = [
                'no' => $k,
                'type' => $v['Type'],
                'null' => $v['Null'],
                'extra' => $v['Extra'],
                'default' => $v['Default'],
                'comment' => $v['Comment'],
            ];
        }
        return $fields;
    }

    /**
     * 获取实际数据表索引的定义
     *
     * @return array 实际数据表索引的定义
     */
    public function get_table_index() {
        $sql = 'show index from `' . static::TBNAME . '`';
        $data = $this->_db->get_query($sql);
        $index = [];

        foreach ($data as $v) {
            $name = $v['Key_name'];
            if (!isset($index[$name])) {
                if ($name == 'PRIMARY') {
                    $index[$name] = [
                        'fields' => [
                            $v['Column_name']
                        ]
                    ];
                } else {
                    $index[$name] = [
                        'fields' => [
                            $v['Column_name']
                        ]
                    ];
                    if ($v['Non_unique'] == 0) {
                        $index[$name]['unique'] = true;
                    } elseif ($v['Index_type'] == 'FULLTEXT') {
                        $index[$name]['fulltext'] = true;
                    }
                }
            } else {
                $index[$name]['fields'][] = $v['Column_name'];
            }
        }
        return $index;
    }

    ///////////////////////////////////////////////////

    /**
     * 私有构造函数
     */
    protected function __construct($args = []) {
        $this->_db = DB::get_default($args);
    }

    /**
     * 虚函数，用于处理用户的输入，生成执行插入和修改的数据值
     */
    abstract protected function _prase($post, $type);
}
