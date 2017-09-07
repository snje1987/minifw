<?php

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

    const DEFAULT_ENGINE = 'InnoDB';
    const DEFAULT_CHARSET = 'utf8';

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

    public function query($sql, $field = [], $value = []) {
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
        $sql = $this->compile_sql($sql, $field, $value);
        $ret = $this->_mysqli->query($sql);
        if ($ret === false && DEBUG == 1) {
            throw new Exception($this->last_error() . "\n" . $sql);
        }
        return $ret;
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

    public function get_table_field($tbname) {
        $sql = 'SHOW FULL FIELDS FROM `' . $tbname . '`';
        $data = $this->get_query($sql);
        if ($data === false) {
            throw new Exception('数据表不存在:' . $tbname);
        }
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

    public function get_table_index($tbname) {
        $sql = 'SHOW INDEX FROM `' . $tbname . '`';
        $data = $this->get_query($sql);
        if ($data === false) {
            throw new Exception('数据表不存在:' . $tbname);
        }
        $index = [];
        foreach ($data as $v) {
            $name = $v['Key_name'];
            if (!isset($index[$name])) {
                $index[$name] = [
                    'fields' => [
                        $v['Column_name']
                    ]
                ];
                $index[$name]['comment'] = $v['Index_comment'];
                if ($name !== 'PRIMARY') {
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

    public function get_table_status($tbname) {
        $sql = 'SHOW CREATE TABLE `' . $tbname . '`';
        $data = $this->get_query($sql);
        if ($data === false || count($data) !== 1) {
            throw new Exception('数据表不存在:' . $tbname);
        }
        $create_sql = $data[0]['Create Table'];
        $matches = [];
        if (preg_match('/ENGINE=(\w+)( AUTO_INCREMENT=\d+)? DEFAULT CHARSET=(\w+)( COMMENT=\'([^\']*)\')?$/', $create_sql, $matches)) {
            $ret = [
                'engine' => $matches[1],
                'charset' => $matches[3],
                'comment' => '',
            ];
            if (isset($matches[5]) && $matches[5] != '') {
                $ret['comment'] = $matches[5];
            }
            return $ret;
        }
        throw new Exception('返回信息处理失败');
    }

    public function compile_sql($sql, $field = [], $value = []) {
        foreach ($field as $k => $v) {
            if (is_array($v)) {
                if ($v[0] === 'expr') {
                    $sql = str_replace("{{$k}}", $v[1], $sql);
                } else {
                    $sql = str_replace("{{$k}}", "`{$v[1]}`", $sql);
                }
            } else {
                $sql = str_replace("{{$k}}", "`{$v}`", $sql);
            }
        }
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                switch ($v[0]) {
                    case 'expr':
                        $sql = str_replace("{{$k}}", $v[1], $sql);
                        break;
                    case 'rich':
                        $v[1] = $this->parse_richstr($v[1]);
                        $sql = str_replace("{{$k}}", "\"{$v[1]}\"", $sql);
                        break;
                    case 'like':
                        $v[1] = $this->parse_like($v[1]);
                        $sql = str_replace("{{$k}}", "\"{$v[1]}\"", $sql);
                        break;
                    default :
                        $v[1] = $this->parse_str($v[1]);
                        $sql = str_replace("{{$k}}", "\"{$v[1]}\"", $sql);
                }
            } else {
                $v = $this->parse_str($v);
                $sql = str_replace("{{$k}}", "\"{$v}\"", $sql);
            }
        }
        return $sql;
    }

    public static function create_table_sql($tbname, $tbinfo, $field, $index, $dim = '') {
        $engine = isset($tbinfo['engine']) ? $tbinfo['engine'] : self::DEFAULT_ENGINE;
        $charset = isset($tbinfo['charset']) ? $tbinfo['charset'] : self::DEFAULT_CHARSET;
        $comment = isset($tbinfo['comment']) ? $tbinfo['comment'] : '';

        if ($tbname === '' || $engine === '' || $charset == '') {
            throw new Exception('参数错误');
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $tbname . "` (" . $dim;
        $lines = [];
        foreach ($field as $k => $v) {
            $lines[] = self::field_to_sql($k, $v);
        }

        foreach ($index as $k => $v) {
            $lines[] = self::index_to_sql($k, $v);
        }

        $sql .= implode("," . $dim, $lines) . $dim;
        $sql .= ') ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset;
        if ($comment != '') {
            $sql .= ' COMMENT="' . $comment . '"';
        }
        return $sql;
    }

    private static function move_field_no(&$fields, $from, $to = -1) {
        foreach ($fields as $k => $v) {
            if ($v['no'] >= $from && ($to < 0 || $v['no'] < $to)) {
                $fields[$k]['no'] ++;
            }
        }
    }

    public static function get_field_diff($tbname, $from, $to) {
        $diff = [];
        $tail = ' first';
        $i = 0;
        foreach ($to as $k => $v) {
            $to_sql = self::field_to_sql($k, $v);
            if (!isset($from[$k])) {
                $diff[] = [
                    'diff' => '+[' . $i . '] ' . $to_sql,
                    'trans' => 'ALTER TABLE `' . $tbname . '` ADD ' . $to_sql . $tail . ';',
                ];
                self::move_field_no($from, $i);
            } else {
                $from_sql = self::field_to_sql($k, $from[$k]);
                if ($from_sql != $to_sql || $i != $from[$k]['no']) {
                    $diff[] = [
                        'diff' => '-[' . $from[$k]['no'] . '] ' . $from_sql . "\n" . '+[' . $i . '] ' . $to_sql,
                        'trans' => 'ALTER TABLE `' . $tbname . '` CHANGE `' . $k . '` ' . $to_sql . $tail . ';',
                    ];
                }
                self::move_field_no($from, $i, $from[$k]['no']);
            }
            $tail = ' after `' . $k . '`';
            $i ++;
        }

        foreach ($from as $k => $v) {
            if (array_key_exists($k, $to)) {
                continue;
            }
            $from_sql = self::field_to_sql($k, $v);
            $diff[] = [
                'diff' => '- ' . $from_sql,
                'trans' => 'ALTER TABLE `' . $tbname . '` DROP `' . $k . '`;',
            ];
        }

        return $diff;
    }

    public static function get_index_diff($tbname, $from, $to) {
        $diff = [];
        foreach ($to as $k => $v) {
            $to_sql = self::index_to_sql($k, $v, false);
            if (!isset($from[$k])) {
                $diff[] = [
                    'diff' => '+ ' . $to_sql,
                    'trans' => 'ALTER TABLE `' . $tbname . '` ADD ' . $to_sql . ';',
                ];
                continue;
            }
            $from_sql = self::index_to_sql($k, $from[$k], false);
            if ($from_sql != $to_sql) {
                $trans = 'ALTER TABLE `' . $tbname . '` DROP';
                if ($k == 'PRIMARY') {
                    $trans .= ' PRIMARY KEY';
                } else {
                    $trans .= ' INDEX `' . $k . '`';
                }
                $trans .= ', ADD ' . $to_sql . ';';
                $diff[] = [
                    'diff' => '- ' . $from_sql . "\n" . '+ ' . $to_sql,
                    'trans' => $trans,
                ];
                continue;
            }
        }

        foreach ($from as $k => $v) {
            if (array_key_exists($k, $to)) {
                continue;
            }
            $from_sql = self::index_to_sql($k, $v, false);
            $trans = 'ALTER TABLE `' . $tbname . '` DROP INDEX `' . $k . '`;';
            if ($k == 'PRIMARY') {
                $trans = 'ALTER TABLE `' . $tbname . '` DROP PRIMARY KEY;';
            }

            $diff[] = [
                'diff' => '- ' . $from_sql,
                'trans' => $trans,
            ];
        }

        return $diff;
    }

    public static function get_status_diff($tbname, $from, $to) {
        $diff = [];
        if ($from['engine'] != $to['engine']) {
            $diff[] = [
                'diff' => '- Engine=' . $from['engine'] . "\n" . '+ Engine=' . $to['engine'],
                'trans' => 'ALTER TABLE `' . $tbname . '` ENGINE=' . $to['engine'] . ';',
            ];
        }
        if ($from['comment'] != $to['comment']) {
            $diff[] = [
                'diff' => '- Comment="' . $from['comment'] . "\"\n" . '+ Comment="' . $to['comment'] . '"',
                'trans' => 'ALTER TABLE `' . $tbname . '` COMMENT="' . $to['comment'] . '";',
            ];
        }
        if ($from['charset'] != $to['charset']) {
            $diff[] = [
                'diff' => '- Charset="' . $from['charset'] . "\"\n" . '+ Charset="' . $to['charset'] . '"',
                'trans' => 'ALTER TABLE `' . $tbname . '` DEFAULT CHARSET="' . $to['charset'] . '";',
            ];
        }
        return $diff;
    }

    protected static function field_to_sql($name, $attr) {
        $sql = '';
        switch ($attr['type']) {
            case 'text':
                $sql = '`' . $name . '` text';
                if (!isset($attr['null']) || $attr['null'] === 'NO') {
                    $sql .= ' NOT NULL';
                }
                break;
            default :
                $sql = '`' . $name . '` ' . $attr['type'];
                if (!isset($attr['null']) || $attr['null'] === 'NO') {
                    $sql .= ' NOT NULL';
                }
                if (isset($attr['extra']) && $attr['extra'] !== null && $attr['extra'] !== '') {
                    $sql .= ' ' . $attr['extra'];
                }
                if (isset($attr['default']) && $attr['default'] !== null) {
                    $sql .= ' DEFAULT "' . $attr['default'] . '"';
                }
                break;
        }
        if (isset($attr['comment']) && $attr['comment'] !== null) {
            $sql .= ' COMMENT "' . $attr['comment'] . '"';
        }
        return $sql;
    }

    protected static function index_to_sql($name, $attr, $in_create = true) {
        $sql = '';
        switch ($name) {
            case 'PRIMARY':
                $sql = 'PRIMARY KEY (`' . implode('`,`', $attr['fields']) . '`)';
                break;
            default :
                if ($in_create) {
                    if (isset($attr['unique']) && $attr['unique'] === true) {
                        $sql = 'UNIQUE ';
                    } else if (isset($attr['fulltext']) && $attr['fulltext'] === true) {
                        $sql = 'FULLTEXT ';
                    }
                    $sql .= 'KEY ';
                } else {
                    if (isset($attr['unique']) && $attr['unique'] === true) {
                        $sql = 'UNIQUE ';
                    } elseif (isset($attr['fulltext']) && $attr['fulltext'] === true) {
                        $sql = 'FULLTEXT ';
                    } else {
                        $sql = 'INDEX ';
                    }
                }
                $sql .= '`' . $name . '` (`' . implode('`,`', $attr['fields']) . '`)';
                break;
        }
        if (isset($attr['comment']) && $attr['comment'] != '') {
            $sql .= ' COMMENT "' . $attr['comment'] . '"';
        }
        return $sql;
    }

    public static function drop_table_sql($tbname) {
        return 'DROP TABLE IF EXISTS `' . $tbname . '`';
    }

}
