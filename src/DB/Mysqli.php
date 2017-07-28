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

namespace Org\Snje\Minifw\DB;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Mysqli extends FW\DB {

    /**
     * @var \mysqli
     */
    protected $_mysqli;
    protected $_encoding;
    protected $_host;
    protected $_username;
    protected $_password;
    protected $_dbname;
    protected $_rollback = false;

    protected function __construct($args = []) {
        parent::__construct();
        $config = FW\Config::get();
        $ini = $config->get_config('mysql');
        if (!empty($args)) {
            $ini['host'] = isset($args['host']) ? strval($args['host']) : $ini['host'];
            $ini['username'] = isset($args['username']) ? strval($args['username']) : $ini['username'];
            $ini['password'] = isset($args['password']) ? strval($args['password']) : $ini['password'];
            $ini['dbname'] = isset($args['dbname']) ? strval($args['dbname']) : $ini['dbname'];
            $ini['encoding'] = isset($args['encoding']) ? strval($args['encoding']) : $ini['encoding'];
        }

        if (empty($ini)) {
            throw new Exception('数据库未配置');
        }
        $this->_host = $ini['host'];
        $this->_username = $ini['username'];
        $this->_password = $ini['password'];
        $this->_dbname = $ini['dbname'];
        $this->_encoding = $ini['encoding'];
        $this->_mysqli = new \mysqli($this->_host, $this->_username, $this->_password, $this->_dbname);
        if ($this->_mysqli->connect_error) {
            throw new Exception('数据库连接失败');
        }
        if (!$this->_mysqli->set_charset($this->_encoding)) {
            throw new Exception('数据库查询失败');
        }
    }

    public function last_insert_id() {
        return $this->_mysqli->insert_id;
    }

    public function last_error() {
        return $this->_mysqli->error;
    }

    public function query($sql) {
        //echo $sql.'<br />';
        //$sql .= 'ddd';
        if (!$this->_mysqli->ping()) {
            @$this->_mysqli->close();
            $this->_mysqli = new mysqli($this->_host, $this->_username, $this->_password, $this->_dbname);
            if ($this->_mysqli->connect_error) {
                throw new Exception('数据库连接失败');
            }
            if (!$this->_mysqli->set_charset($this->_encoding)) {
                throw new Exception('数据库查询失败');
            }
        }
        return $this->_mysqli->query($sql);
    }

    public function fetch_all($res) {
        if (method_exists('mysqli_result', 'fetch_all')) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            for ($data = []; $tmp = $res->fetch_array(MYSQLI_ASSOC);) {
                $data[] = $tmp;
            }
        }
        return $data;
    }

    public function fetch($res) {
        return $res->fetch_array(MYSQLI_ASSOC);
    }

    public function free($res) {
        return $res->free();
    }

    public function parse_str($str) {
        $str = htmlspecialchars(trim($str));
        $str = $this->_mysqli->escape_string($str);
        return $str;
    }

    public function parse_richstr($str) {
        $str = $this->_mysqli->escape_string($str);
        return trim($str);
    }

    public function parse_like($str) {
        $str = $this->_mysqli->escape_string($str);
        $str = str_replace("_", "\_", $str);
        $str = str_replace("%", "\%", $str);
        return trim($str);
    }

    public function multi_query($sql) {
        return $this->_mysqli->multi_query($sql);
    }

    protected function _begin() {
        $this->query('SET AUTOCOMMIT=0');
        $this->query('BEGIN');
        $this->_rollback = true;
    }

    protected function _commit() {
        if ($this->_rollback) {
            $this->query('COMMIT');
            $this->query('SET AUTOCOMMIT=1');
            $this->_rollback = false;
        }
    }

    protected function _rollback() {
        if ($this->_rollback) {
            $this->query('ROLLBACK');
            $this->query('SET AUTOCOMMIT=1');
            $this->_rollback = false;
        }
    }

}
