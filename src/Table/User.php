<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class User extends Table {

    public static $tbname = DBPREFIX . 'user';
    protected $current_user_data = null;

    const EXPIRE_TIME = 36000;
    const SESSION = 'user_id';
    const TOKEN = 'user_token';
    const SECODE_KEY = 'user_secode';

    public function clear($time) {
        $value = array();
        $value['last_time'] = 0;
        $value['login'] = '';
        $value['ip'] = '';

        $condition = array();
        $condition['last_time'] = array('<', $time);
        $this->db->update(static::$tbname, $value, $condition);
    }

    public function validate_login() {
        $now = time();
        if (static::EXPIRE_TIME > 0) {
            $time = $now - static::EXPIRE_TIME;
            $this->clear($time);
        }

        $uid = intval($_SESSION[static::SESSION]);
        $token = strval($_SESSION[static::TOKEN]);

        if ($uid == 0 || $token == '') {
            return false;
        }

        $token1 = $_GET['token'];
        if ($token1 == '') {
            $token1 = $_POST['token'];
        }
        if ($token1 == '' || $token != $token1) {
            return false;
        }
        $udata = $this->get_by_id($uid);
        if (empty($udata) || $udata['disable'] == 1) {
            return false;
        }
        $sid = session_id();
        if ($udata['login'] != '' && $udata['login'] == md5($udata['password'] . $sid)) {
            $this->set_field($uid, 'last_time', $now);
            return true;
        } else {
            unset($_SESSION[static::SESSION]);
            unset($_SESSION[static::TOKEN]);
            return false;
        }
    }

    public function login($post) {
        $username = strval($post['username']);
        $password = trim(strval($post['password']));
        $code = strval($post['code']);
        if (!FW\Secoder::test($code, static::SECODE_KEY)) {
            throw new Exception('验证码错误');
        }
        $udata = $this->get_by_username($username);
        if (empty($udata) || $udata['disable'] != 0) {
            throw new Exception('用户名或密码错误');
        }
        if (md5($password . $udata['key']) != $udata['password']) {
            throw new Exception('用户名或密码错误');
        }

        $value = array();
        $value['login'] = md5($udata['password'] . session_id());
        $value['ip'] = $_SERVER['REMOTE_ADDR'];
        $value['last_time'] = time();

        $condition = array();
        $condition['id'] = $udata['id'];

        if (!$this->db->update(static::$tbname, $value, $condition)) {
            throw new Exception('登录出错');
        }

        $_SESSION[static::TOKEN] = md5(FW\Random::gen_str(15));
        $_SESSION[static::SESSION] = $udata['id'];
        return true;
    }

    public function kick($args) {
        $id = intval($args[0]);
        $value = array();
        $value['last_time'] = 0;
        $value['login'] = '';
        $value['ip'] = '';

        $condition = array();
        $condition['id'] = $id;
        return $this->db->update(static::$tbname, $value, $condition);
    }

    public function logout() {
        $value = array();
        $value['last_time'] = 0;
        $value['login'] = '';
        $value['ip'] = '';

        $condition = array();
        $condition['id'] = intval($_SESSION[static::SESSION]);
        $this->db->update(static::$tbname, $value, $condition);
        unset($_SESSION[static::SESSION]);
        unset($_SESSION[static::TOKEN]);
        return true;
    }

    public function get_by_username($username) {
        $condition = array();
        $condition['username'] = strval($username);
        return $this->db->one_query(static::$tbname, $condition);
    }

    public function get_user_data() {
        if (isset($_SESSION[static::SESSION]) && $_SESSION[static::SESSION] != 0) {
            if ($this->current_user_data == null) {
                $id = $_SESSION[static::SESSION];
                $this->current_user_data = $this->get_by_id($id);
            }
            return $this->current_user_data;
        } else {
            return false;
        }
    }

    public function add($post) {
        $data = $this->_prase($post, 1);
        $ret = $this->db->insert(static::$tbname, $data);
        if (!$ret) {
            return false;
        }
        return true;
    }

    public function edit($post) {
        $data = $this->_prase($post, 2);
        $id = intval($post['id']);
        if ($id == 0) {
            return false;
        }
        $condition = array();
        $condition['id'] = $id;
        $ret = $this->db->update(static::$tbname, $data, $condition);
        if (!$ret) {
            return false;
        }
        return true;
    }

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        $value = array();

        $pwd1 = trim(strval($post['password1']));
        $pwd2 = trim(strval($post['password2']));
        if ($type == 1) {
            if ($pwd1 == '') {
                throw new Exception('密码不能为空');
            }
        }
        if ($pwd1 != $pwd2) {
            throw new Exception('密码与确认密码不符');
        }
        $username = strval($post['username']);
        if ($username == '') {
            throw new Exception('用户名不能为空');
        }
        $data = $this->get_by_username($username);
        if (!empty($data) && ($type == 1 || $data['id'] != $post['id'])) {
            throw new Exception('用户名已存在');
        }
        $value['username'] = $username;
        $value['truename'] = strval($post['truename']);

        if (isset($post['disable']) && $post['disable'] != 0) {
            $value['disable'] = 1;
        } else {
            $value['disable'] = 0;
        }

        if ($pwd1 != '') {
            $value['key'] = array('rich', \Site\Random::gen_key(15));
            $value['password'] = md5($pwd1 . $value['key'][1]);
        }
        return $value;
    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '用户',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => '管理员ID',),
        'username' => array('type' => 'varchar(20)', 'comment' => '用户名',),
        'password' => array('type' => 'varchar(64)', 'comment' => '密码',),
        'key' => array('type' => 'varchar(20)', 'comment' => '秘钥',),
        'truename' => array('type' => 'varchar(20)', 'comment' => '姓名',),
        'login' => array('type' => 'varchar(64)', 'default' => '', 'comment' => '登录秘钥',),
        'ip' => array('type' => 'varchar(20)', 'default' => '', 'comment' => '登录IP',),
        'last_time' => array('type' => 'int(11)', 'default' => '0', 'comment' => '最后活动时间',),
        'disable' => array('type' => 'tinyint(4)', 'default' => '0', 'comment' => '是否停用',),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'username' => array('unique' => true, 'fields' => array('username')),
    );

}
