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

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

/**
 * 系统的相关操作
 */
class System {

    /**
     * @var static the instance
     */
    protected static $_instance = null;

    public static function get($args = array()) {
        if (self::$_instance === null) {
            self::$_instance = new static($args);
        }
        return self::$_instance;
    }

    public static function get_new($args = array()) {
        if (self::$_instance !== null) {
            self::$_instance = null;
        }
        return self::get($args);
    }

    protected $_calls = array();

    /**
     * @var Org\Snje\Minifw\Config
     */
    protected $config;
    protected $errors = array();
    protected $use_buffer = false;

    protected function __construct($cfg_path) {

        $_GET = self::magic_gpc($_GET);
        $_POST = self::magic_gpc($_POST);
        $_COOKIE = self::magic_gpc($_COOKIE);

        if (!file_exists($cfg_path)) {
            die('配置文件不存在');
        }
        $this->config = Config::get_new($cfg_path);
        if (!defined('WEB_ROOT')) {
            $web_root = $this->config->get_config('path', 'web_root', '');
            $web_root = rtrim(str_replace('\\', '/', $web_root));
            if ($web_root == '') {
                die('未指定WEB_ROOT.');
            }
            define('WEB_ROOT', $web_root);
        }
        if (!defined('DEBUG')) {
            define('DEBUG', $this->config->get_config('debug', 'debug', 0));
        }
        if (!defined('DBPREFIX')) {
            define('DBPREFIX', $this->config->get_config('main', 'dbprefix', ''));
        }
        date_default_timezone_set($this->config->get_config('main', 'timezone', 'UTC'));

        //设置错误处理函数
        set_error_handler(array($this, 'captureNormal'));
        //设置异常处理函数
        set_exception_handler(array($this, 'captureException'));
        //设置停机处理函数
        register_shutdown_function(array($this, 'captureShutdown'));
        header('Content-type:text/html;charset=' . $this->config->get_config('main', 'encoding', 'utf-8'));
    }

    public function run() {
        $path = $this->config->get_config('main', 'uri', '/');
        try {
            foreach ($this->_calls as $v) {
                $matches = array();
                if (preg_match($v['reg'], $path, $matches) === 1) {
                    if (!isset($v['option']['session']) || $v['option']['session']) {
                        $this->_set_seesion();
                    }
                    if (!isset($v['option']['buffer']) || $v['option']['buffer']) {
                        ob_start();
                        $this->use_buffer = true;
                    }
                    array_shift($matches);
                    call_user_func_array($v['callback'], $matches);
                    if ($this->use_buffer) {
                        if (DEBUG === 1 && !empty($this->errors)) {
                        $content = ob_get_clean();
                        print_r($this->errors);
                        echo $content;
                    } else {
                        @ob_end_flush();
                    }
                    } else {
                        if (DEBUG === 1 && !empty($this->errors)) {
                            print_r($this->errors);
                        }
                    }
                    return;
                }
            }
        } catch (\Exception $ex) {
            if ($this->use_buffer) {
                @ob_end_clean();
            }
            if (DEBUG === 1) {
                $controler = new Controler();
                $controler->show_msg($ex->getMessage(), 'Error');
                return;
            }
            return;
        }
        if ($this->use_buffer) {
            @ob_end_clean();
        }
        $controler = new Controler();
        if (DEBUG === 1) {
            $controler->show_msg('路由未指定.', 'Error');
        } else {
            $controler->show_404();
        }
    }

    public function reg_call($reg, $callback, $option = array()) {
        $this->_calls[] = array(
            'reg' => $reg,
            'callback' => $callback,
            'option' => $option,
        );
    }

    protected function _set_seesion() {
        $session_name = $this->config->get_config('main', 'session', 'PHPSESSION');
        session_name($session_name);
        session_set_cookie_params(36000, '/', $this->config->get_config('main', 'domain', ''));

        //处理Flash丢失cookie的问题
        $session_id = '';
        isset($_POST[$session_name]) && $session_id = strval($_POST[$session_name]);
        if ($session_id == '') {
            isset($_GET[$session_name]) && $session_id = strval($_GET[$session_name]);
        }
        if ($session_id != '') {
            session_id($session_id);
        }
        session_start();
    }

    /**
     * 处理用户发送的数据，执行trim和去除多余的转义
     * 这里要求php版本>=5.6，已经移除的自动转义功能，所以不用再处理转义符
     *
     * @param mixed $string 要处理的数据
     * @return mixed 处理后的数据
     */
    public static function magic_gpc($string) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = self::magic_gpc($val);
            }
        } else {
            $string = trim($string);
        }
        return $string;
    }

    public function captureNormal($number, $message, $file, $line) {
        if (DEBUG === 1) {
            $this->errors[] = array('type' => $number, 'message' => $message, 'file' => $file, 'line' => $line);
        }
    }

    public function captureException($exception) {
        if ($this->use_buffer) {
            @ob_end_clean();
        }
        if (DEBUG === 1) {
            header('Content-type:text/plain;charset=' . $this->config->get_config('main', 'encoding', 'utf-8'));
            print_r($exception);
        } else {
            echo 'Runtime Error';
        }
    }

    public function captureShutdown() {
        $error = error_get_last();
        if ($error) {
            if ($this->use_buffer) {
                @ob_end_clean();
            }
            if (DEBUG === 1) {
                header('Content-type:text/plain;charset=' . $this->config->get_config('main', 'encoding', 'utf-8'));
                print_r($error);
            } else {
                echo 'Runtime Error';
            }
        } else {
            return true;
        }
    }

}
