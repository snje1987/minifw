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

namespace Org\Snje\Minifw\DB;

use Org\Snje\Minifw as Minifw;

class Mysqli extends Minifw\DB {

    /**
     * @var Minifw\DB\Mysqli 唯一的实例
     */
    protected static $_instance = [];

    /**
     * @var \mysqli mysqli连接
     */
    protected $_mysqli;

    /**
     * @var string 数据库连接的字符编码
     */
    protected $_encoding;

    /**
     * @var string 数据库的地址
     */
    protected $_host;

    /**
     * @var string 数据库用户名
     */
    protected $_username;

    /**
     * @var string 数据库密码
     */
    protected $_password;

    /**
     * @var stirng 数据库名称
     */
    protected $_dbname;

    /**
     * @var bool 标记数据库是否可以进行roolback
     */
    protected $_rollback = false;

    /**
     * 获取数据库唯一的实例
     *
     * @return Minifw\DB\Mysqli 数据库唯一的实例
     */
    public static function get_instance($args = []) {
        $id = '';
        if (!empty($args)) {
            $id = strval($args['id']);
        }
        if (!isset(self::$_instance[$id])) {
            self::$_instance[$id] = new Mysqli($args);
        }
        return self::$_instance[$id];
    }

    /**
     * 返回上一条语句插入的数据的自增字段的数值
     *
     * @return int 自增字段的数值
     */
    public function last_insert_id() {
        return $this->_mysqli->insert_id;
    }

    /**
     * 开启事务
     */
    public function begin() {
        $this->_query('SET AUTOCOMMIT=0');
        $this->_query('BEGIN');
        $this->_rollback = true;
    }

    /**
     * 提交事务
     */
    public function commit() {
        if ($this->_rollback) {
            $this->_query('COMMIT');
            $this->_query('SET AUTOCOMMIT=1');
            $this->_rollback = false;
        }
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        if ($this->_rollback) {
            $this->_query('ROLLBACK');
            $this->_query('SET AUTOCOMMIT=1');
            $this->_rollback = false;
        }
    }

    public function last_error() {
        return $this->_mysqli->error;
    }

    /*     * ********************************************************** */

    /**
     * 私有构造函数
     */
    protected function __construct($args = []) {
        parent::__construct();
        $ini = Minifw\Config::get('mysql');
        if (!empty($args)) {
            $ini['host'] = isset($args['host']) ? strval($args['host']) : $ini['host'];
            $ini['username'] = isset($args['username']) ? strval($args['username']) : $ini['username'];
            $ini['password'] = isset($args['password']) ? strval($args['password']) : $ini['password'];
            $ini['dbname'] = isset($args['dbname']) ? strval($args['dbname']) : $ini['dbname'];
            $ini['encoding'] = isset($args['encoding']) ? strval($args['encoding']) : $ini['encoding'];
        }

        if (empty($ini)) {
            throw new Minifw\Exception('数据库未配置');
        }
        $this->_host = $ini['host'];
        $this->_username = $ini['username'];
        $this->_password = $ini['password'];
        $this->_dbname = $ini['dbname'];
        $this->_encoding = $ini['encoding'];
        $this->_mysqli = new \mysqli($this->_host, $this->_username, $this->_password, $this->_dbname);
        if ($this->_mysqli->connect_error) {
            throw new Minifw\Exception('数据库连接失败');
        }
        if (!$this->_mysqli->set_charset($this->_encoding)) {
            throw new Minifw\Exception('数据库查询失败');
        }
    }

    /**
     * 执行sql查询，返回结果
     *
     * @param string $sql 要执行的查询
     * @return mixed 查询的结果
     */
    protected function _query($sql) {
        //echo $sql.'<br />';
        //$sql .= 'ddd';
        if (!$this->_mysqli->ping()) {
            @$this->_mysqli->close();
            $this->_mysqli = new mysqli($this->_host, $this->_username, $this->_password, $this->_dbname);
            if ($this->_mysqli->connect_error) {
                throw new Minifw\Exception('数据库连接失败');
            }
            if (!$this->_mysqli->set_charset($this->_encoding)) {
                throw new Minifw\Exception('数据库查询失败');
            }
        }
        return $this->_mysqli->query($sql);
    }

    /**
     * 将sql查询结果全部转化成数组
     *
     * @param \mysqli_result $res 要转化的查询
     * @return array 查询的结果
     */
    protected function _fetch_all($res) {
        if (method_exists('mysqli_result', 'fetch_all')) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            for ($data = []; $tmp = $res->fetch_array(MYSQLI_ASSOC);) {
                $data[] = $tmp;
            }
        }
        return $data;
    }

    /**
     * 从sql查询的结果中获取一条数据
     *
     * @param \mysqli_result $res sql查询结果
     * @return array 获取的数据，或者false
     */
    protected function _fetch($res) {
        return $res->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * 释放sql查询结果
     *
     * @param \mysqli_result $res 要释放的结果
     * @return bool 成功返回true，失败返回false
     */
    protected function _free($res) {
        return $res->free();
    }

    /**
     * 转义用于sql查询的字符串，转义所有html特殊字符和sql特殊字符
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_str($str) {
        $str = htmlspecialchars(trim($str));
        $str = $this->_mysqli->escape_string($str);
        return $str;
    }

    /**
     * 转义sql特殊字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_richstr($str) {
        $str = $this->_mysqli->escape_string($str);
        return trim($str);
    }

    /**
     * 转义用于执行like查询的字符串
     *
     * @param string $str 要转义的字符串
     * @return string 转义的结果
     */
    protected function _parse_like($str) {
        $str = $this->_mysqli->escape_string($str);
        $str = str_replace("_", "\_", $str);
        $str = str_replace("%", "\%", $str);
        return trim($str);
    }

}
