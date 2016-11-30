<?php

/*
 * Copyright (C) 2015 Yang Ming <yangming0116@163.com>
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
 * @filename Sqlite.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2015-10-2  14:04:47
 * @version 1.0
 * @Description 定义Sqlite数据库的相关操作
 */

namespace Org\Snje\Minifw\DB;

use Org\Snje\Minifw as Minifw;

class SQLite extends Minifw\DB {

    /**
     * @var Minifw\DB\Sqlite 唯一的实例
     */
    protected static $_instance = [];

    /**
     * @var \SQLite3 sqlite连接
     */
    protected $_sqlite;

    /**
     * @var bool 标记数据库是否可以进行roolback
     */
    protected $_rollback = false;

    /**
     * 获取数据库唯一的实例
     *
     * @return Minifw\DB\Sqlite 数据库唯一的实例
     */
    public static function get_instance($args = []) {
        $id = '';
        if (!empty($args)) {
            $id = strval($args['id']);
        }
        if (!isset(self::$_instance[$id])) {
            self::$_instance[$id] = new Sqlite($args);
        }
        return self::$_instance[$id];
    }

    /**
     * 返回上一条语句插入的数据的自增字段的数值
     *
     * @return int 自增字段的数值
     */
    public function last_insert_id() {
        return $this->_sqlite->lastInsertRowID();
    }

    /**
     * 开启事务
     */
    protected function _begin() {
        $this->_query('begin');
        $this->_rollback = true;
    }

    /**
     * 提交事务
     */
    protected function _commit() {
        if ($this->_rollback) {
            $this->_query('COMMIT');
            $this->_rollback = false;
        }
    }

    /**
     * 回滚事务
     */
    protected function _rollback() {
        if ($this->_rollback) {
            $this->_query('ROLLBACK');
            $this->_rollback = false;
        }
    }

    public function last_error() {
        return $this->_sqlite->lastErrorMsg();
    }

    /*     * ********************************************************** */

    /**
     * 私有构造函数
     */
    protected function __construct($args = []) {
        parent::__construct();
        $ini = Minifw\Config::get('sqlite');
        if (!empty($args)) {
            $ini['path'] = isset($args['path']) ? strval($args['path']) : $ini['path'];
        }

        if (empty($ini)) {
            throw new Minifw\Exception('数据库未配置');
        }
        $this->_sqlite = new \SQLite3(WEB_ROOT . $ini['path'], SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    }

    /**
     * 执行sql查询，返回结果
     *
     * @param string $sql 要执行的查询
     * @return mixed 查询的结果
     */
    protected function _query($sql) {
        return $this->_sqlite->query($sql);
    }

    /**
     * 将sql查询结果全部转化成数组
     *
     * @param \SQLite3Result $res 要转化的查询
     * @return array 查询的结果
     */
    protected function _fetch_all($res) {
        for ($data = []; $tmp = $res->fetchArray(MYSQLI_ASSOC);) {
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * 从sql查询的结果中获取一条数据
     *
     * @param \SQLite3Result $res sql查询结果
     * @return array 获取的数据，或者false
     */
    protected function _fetch($res) {
        return $res->fetchArray(MYSQLI_ASSOC);
    }

    /**
     * 释放sql查询结果
     *
     * @param \SQLite3Result $res 要释放的结果
     * @return bool 成功返回true，失败返回false
     */
    protected function _free($res) {
        return $res->finalize();
    }

    /**
     * 转义用于sql查询的字符串，转义所有html特殊字符和sql特殊字符
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_str($str) {
        $str = htmlspecialchars(trim($str));
        $str = str_replace("\"", "\"\"", $str);
        return $str;
    }

    /**
     * 转义sql特殊字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_richstr($str) {
        $str = str_replace("\"", "\"\"", $str);
        return trim($str);
    }

    /**
     * 转义用于执行like查询的字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_like($str) {
        $str = str_replace(
                ["/", "'", "\"", "[", "]", "%", "&", "_", "(", ")"], ["//", "''", "\"\"", "/[", "/]", "/%", "/&", "/_", "/(", "/)"], $str
        );
        return trim($str);
    }

}
