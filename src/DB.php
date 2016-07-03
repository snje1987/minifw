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
 * @datetime 2013-3-27 9:21:28
 * @version 1.0
 * @Description 定义Mysqli数据库的相关操作
 */

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as Minifw;

abstract class DB {
    /**
     * 共有方法
     */

    /**
     * 获取一个指定类型的数据库实例
     *
     * @return Minifw\DB 数据库唯一的实例
     */
    public static function get($args = []) {
        $type = '';
        if (isset($args['type'])) {
            $type = strval($args['type']);
        }
        if ($type == '') {
            $type = Minifw\Config::get('main', 'db', '');
        }
        if ($type == '') {
            throw new Minifw\Exception("未指定数据库类型");
        }
        $class_name = __NAMESPACE__ . "\\DB\\" . $type;
        if (!class_exists($class_name)) {
            throw new Minifw\Exception("类型不存在");
        }
        return $class_name::get_instance($args);
    }

    /**
     * 执行一个sql查询，返回所有符合条件的结果
     *
     * @param string $tbname 数据表名称
     * @param array $condition 查询的条件，为空则返回所有数据
     * @param array $field 要选取的字段，为空的选取所有字段
     * @return array 查询到的所有的数据
     */
    public function limit_query($tbname, $condition = [], $field = []) {
        $fieldstr = $this->_parse_field($field);
        $conditionstr = $this->_parse_condition($condition, $tbname);
        $sql = 'select ' . $fieldstr . ' from `' . $tbname . '`' . $conditionstr;
        $res = $this->_query($sql);
        if ($res === false) {
            throw new Exception($this->last_error() . '<br />' . $sql);
        }
        $data = $this->_fetch_all($res);
        $this->_free($res);
        return $data;
    }

    /**
     * 执行一个sql查询，返回符合条件的结果
     *
     * @param string $sql 要执行的sql查询
     * @return array 查询的结果，不存在则返回空数组
     */
    public function get_query($sql) {
        $res = $this->_query($sql);
        if ($res === false) {
            throw new Exception($this->last_error() . '<br />' . $sql);
        }
        $data = $this->_fetch_all($res);
        $this->_free($res);
        return $data;
    }

    /**
     * 执行一个sql查询，返回第一个符合条件的数据
     *
     * @param string $tbname 数据表的名称
     * @param array $condition 查询的条件，为空则为所有数据
     * @param array $field 查询的字段，为空则返回所有字段
     * @return array 符合条件的第一条数据
     */
    public function one_query($tbname, $condition = [], $field = []) {
        $fieldstr = $this->_parse_field($field);
        $conditionstr = $this->_parse_condition($condition, $tbname);
        $sql = 'select ' . $fieldstr . ' from `' . $tbname . '`' . $conditionstr . ' limit 1';
        $res = $this->_query($sql);
        if ($res === false) {
            throw new Exception($this->last_error() . '<br />' . $sql);
        }
        $data = $this->_fetch($res);
        $this->_free($res);
        return $data;
    }

    /**
     * 计算符合条件的数据的数量
     *
     * @param string $tbname 数据表的名称
     * @param array $condition 查询的条件
     * @return int 符合条件的数据的数量
     */
    public function count($tbname, $condition = []) {
        $conditionstr = $this->_parse_condition($condition);
        $sql = 'select count(*) as "count" from `' . $tbname . '` ' . $conditionstr;
        $res = $this->_query($sql);
        if ($res === false) {
            throw new Exception($this->last_error() . '<br />' . $sql);
        }
        $data = $this->_fetch($res);
        $this->_free($res);
        return $data['count'];
    }

    /**
     * 插入一条数据
     *
     * @param string $tbname 插入数据的数据表
     * @param array $value 插入的数据各个字段的值
     * @return bool 成功返回true，失败返回false
     */
    public function insert($tbname, $value) {
        $valuestr = $this->_parse_value($value);
        $sql = 'insert into `' . $tbname . '`' . $valuestr;
        return $this->_query($sql);
    }

    /**
     * 根据条件删除数据
     *
     * @param string $tbname 数据表的名称
     * @param array $condition 删除的条件，不能为空
     * @return bool 成功返回true，失败返回false
     */
    public function delete($tbname, $condition = []) {
        if (empty($condition)) {
            throw new Minifw\Exception('删除条件不能为空');
        }
        $conditionstr = $this->_parse_condition($condition);
        $sql = 'delete from `' . $tbname . '`' . $conditionstr;
        return $this->_query($sql);
    }

    /**
     * 根据条件更新数据
     *
     * @param string $tbname 数据表的名称
     * @param array $value 新的数据的值
     * @param array $condition 更新的条件
     * @return bool 成功返回true，失败返回false
     */
    public function update($tbname, $value, $condition = []) {
        if (empty($condition)) {
            throw new Minifw\Exception('更新条件不能为空');
        }
        $updatestr = $this->_parse_update($value);
        $conditionstr = $this->_parse_condition($condition);
        $sql = 'update `' . $tbname . '` set ' . $updatestr . $conditionstr;
        return $this->_query($sql);
    }

    /**
     * 指定一条sql语句，返回结果
     *
     * @param string $sql 要执行的语句
     * @return mixed 返回的结果
     */
    public function query($sql) {
        return $this->_query($sql);
    }

    /**
     * 将sql查询结果全部转化成数组
     *
     * @param \mysqli_result $res 要转化的查询
     * @return array 查询的结果
     */
    public function fetch_all($res) {
        return $this->_fetch_all($res);
    }

    /**
     * 从sql查询的结果中获取一条数据
     *
     * @param \mysqli_result $res sql查询结果
     * @return array 获取的数据，或者false
     */
    public function fetch($res) {
        return $this->_fetch($res);
    }

    /**
     * 释放sql查询结果
     *
     * @param \mysqli_result $res 要释放的结果
     * @return bool 成功返回true，失败返回false
     */
    public function free($res) {
        return $this->_free($res);
    }

    /**
     * 转义用于sql查询的字符串，转义所有html特殊字符和sql特殊字符
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    public function parse_str($str) {
        return $this->_parse_str($str);
    }

    /**
     * 转义sql特殊字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    public function parse_richstr($str) {
        return $this->_parse_richstr($str);
    }

    /**
     * 转义用于执行like查询的字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    public function parse_like($str) {
        return $this->_parse_like($str);
    }

    /**
     * 受保护方法
     */
    protected function __construct($args = []) {

    }

    /**
     * 处理用于指定sql查询的字段名
     *
     * @param string $field 要处理的字段名
     * @return string 处理后得到的sql语句
     */
    protected function _parse_field($field) {
        if (empty($field)) {
            return '*';
        }
        $str = '';
        $first = true;
        foreach ($field as $one) {
            if ($first) {
                $str .= '`' . strval($one) . '`';
                $first = false;
            } else {
                $str .= ',`' . strval($one) . '`';
            }
        }
        return $str;
    }

    /**
     * 预处理用于插入数据的sql查询的字段值
     *
     * @param array $value 要处理的字段值
     * @return string 处理后得到的sql语句
     */
    protected function _parse_value($value) {
        $fieldstr = '';
        $valuestr = '';
        $first = true;
        foreach ($value as $key => $value1) {
            if ($first == true) {
                $first = false;
                $fieldstr .= '`' . $key . '`';
                if (is_array($value1) && $value1[0] == 'rich') {
                    $valuestr .= '"' . ($this->_parse_richstr(strval($value1[1]))) . '"';
                } else {
                    $valuestr .= '"' . ($this->_parse_str(strval($value1))) . '"';
                }
            } else {
                $fieldstr .= ',`' . $key . '`';
                if (is_array($value1) && $value1[0] == 'rich') {
                    $valuestr .= ',"' . ($this->_parse_richstr(strval($value1[1]))) . '"';
                } else {
                    $valuestr .= ',"' . ($this->_parse_str(strval($value1))) . '"';
                }
            }
        }
        return '(' . $fieldstr . ') values (' . $valuestr . ')';
    }

    /**
     * 预处理用于更新查询的sql查询的字段值
     *
     * @param array $value 要处理的字段值
     * @return string 处理后得到的sql语句
     */
    protected function _parse_update($value) {
        $str = '';
        $first = true;
        foreach ($value as $key => $value1) {
            if ($first == true) {
                $first = false;
                if (is_array($value1) && $value1[0] == 'rich') {
                    $str .= '`' . $key . '`="' . ($this->_parse_richstr($value1[1])) . '"';
                } else {
                    $str .= '`' . $key . '`="' . ($this->_parse_str($value1)) . '"';
                }
            } else {
                if (is_array($value1) && $value1[0] == 'rich') {
                    $str .= ',`' . $key . '`="' . ($this->_parse_richstr($value1[1])) . '"';
                } else {
                    $str .= ',`' . $key . '`="' . ($this->_parse_str($value1)) . '"';
                }
            }
        }
        return $str;
    }

    /**
     * 预处理执行sql查询的复合条件
     *
     * @param array $value 条件数组
     * @param bool $first 是否是条件中的第一个
     * @param string $key 条件对应的字段
     * @param string $tbname 条件对应的数据表
     * @return string 处理后得到的sql语句
     */
    protected function _parse_opt($value, &$first, $key, $tbname) {
        $str = '';
        $value[0] = strval($value[0]);
        switch ($value[0]) {
            case '>':
            case '<':
            case '=':
            case '>=':
            case '<=':
            case '<>':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '`' . $value[0] . '"' . ($this->_parse_str($value[1])) . '")';
                break;
            case 'between':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` between "' . ($this->_parse_str($value[1])) . '" and "' . ($this->_parse_str($value[2])) . '")';
                break;
            case 'have':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` like "%' . ($this->_parse_like($value[1])) . '%")';
                break;
            case 'end':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` like "%' . ($this->_parse_like($value[1])) . '")';
                break;
            case 'begin':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` like "' . ($this->_parse_like($value[1])) . '%")';
                break;
            case 'nohave':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` not like "%' . ($this->_parse_like($value[1])) . '%")';
                break;
            case 'in':
                if ($first != true) {
                    $str .= ' and ';
                } else {
                    $first = false;
                }
                $str .= ' (' . ($tbname == '' ? '' : '`' . $tbname . '`.') . '`' . $key . '` in (';
                $str .= 'select `' . ($this->_parse_str($value[2])) . '` from `' . ($this->_parse_str($value[1])) . '`' . $this->_parse_condition($value[3], $this->_parse_str($value[1]));
                $str .= '))';
                break;
            case 'or':
                $tmp_first = true;
                $first = false;
                $str .= ' or (';
                $str .= $this->_parse_opt($value[1], $tmp_first, $key, $tbname);
                $str .= ')';
                break;
            default:
                throw new Minifw\Exception('查询条件错误');
        }
        return $str;
    }

    /**
     * 处理用于执行sql查询的条件数组
     *
     * @param array $condition 要处理的条件数组
     * @param string $tbname 数据表的名称
     * @return string 处理后得到的sql语句
     */
    protected function _parse_condition($condition, $tbname = '') {
        if (empty($condition)) {
            return '';
        }
        $str = '';
        $first = true;
        foreach ($condition as $key => $value) {
            if ($key == 'order') {
                $str .= ' order by ' . $value;
            } elseif ($key == 'limit') {
                $str .= ' limit ' . $value;
            } else {
                if (is_array($value)) {
                    if (is_array($value[0])) {
                        foreach ($value as $one) {
                            $str .= $this->_parse_opt($one, $first, $key, $tbname);
                        }
                    } else {
                        $str .= $this->_parse_opt($value, $first, $key, $tbname);
                    }
                } else {
                    if ($first != true) {
                        $str .= ' and ';
                    } else {
                        $first = false;
                    }
                    if ($tbname != '') {
                        $str .= '`' . $tbname . '` .';
                    }
                    $str .= ' `' . $key . '` = "' . ($this->_parse_str(strval($value))) . '"';
                }
            }
        }
        if ($first == false) {
            $str = ' where ' . $str;
        }
        return $str;
    }

    /**
     * 虚函数
     */

    /**
     * 返回上一条语句插入的数据的自增字段的数值
     *
     * @return int 自增字段的数值
     */
    abstract public function last_insert_id();

    /**
     * 开启事务
     */
    abstract public function begin();

    /**
     * 提交事务
     */
    abstract public function commit();

    /**
     * 回滚事务
     */
    abstract public function rollback();

    abstract public function last_error();

    /**
     * 执行sql查询，返回结果
     *
     * @param string $sql 要执行的查询
     * @return mixed 查询的结果
     */
    abstract protected function _query($sql);

    /**
     * 将sql查询结果全部转化成数组
     *
     * @param \SQLite3Result $res 要转化的查询
     * @return array 查询的结果
     */
    abstract protected function _fetch_all($res);

    /**
     * 从sql查询的结果中获取一条数据
     *
     * @param \SQLite3Result $res sql查询结果
     * @return array 获取的数据，或者false
     */
    abstract protected function _fetch($res);

    /**
     * 释放sql查询结果
     *
     * @param \SQLite3Result $res 要释放的结果
     * @return bool 成功返回true，失败返回false
     */
    abstract protected function _free($res);

    /**
     * 转义用于sql查询的字符串，转义所有html特殊字符和sql特殊字符
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    abstract protected function _parse_str($str);

    /**
     * 转义sql特殊字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    abstract protected function _parse_richstr($str);

    /**
     * 转义用于执行like查询的字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    abstract protected function _parse_like($str);
}
