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

/**
 * 系统的相关操作
 */
class System {

    use Traits\OneInstance;

    protected $_calls = [];

    /**
     * @var Org\Snje\Minifw\Config
     */
    protected $config;

    protected function __construct($cfg_path) {

        $_GET = self::magic_gpc($_GET);
        $_POST = self::magic_gpc($_POST);
        $_COOKIE = self::magic_gpc($_COOKIE);

        if (!file_exists($cfg_path)) {
            die('Config file not found.');
        }
        $this->config = Config::get_new($cfg_path);
        if (!defined('WEB_ROOT')) {
            $web_root = $this->config->get_config('path', 'web_root', '');
            $web_root = rtrim(str_replace('\\', '/', $web_root));
            if ($web_root == '') {
                die('"WEB_ROOT" is not define.');
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
        set_error_handler([__CLASS__, 'captureNormal']);
        //设置异常处理函数
        set_exception_handler([__CLASS__, 'captureException']);
        //设置停机处理函数
        register_shutdown_function([__CLASS__, 'captureShutdown']);
    }

    /**
     * 初始化系统
     */
    public function run() {
        $this->_set_env();
        $this->dispatch($this->config->get_config('main', 'uri', '/'));
    }

    public function reg_call($reg, $callback) {
        $this->_calls[] = [
            'reg' => $reg,
            'callback' => $callback,
        ];
    }

    /**
     * 配置系统的主要参数
     */
    protected function _set_env() {
        ob_start();

        header('Content-type:text/html;charset=' . $this->config->get_config('main', 'encoding', 'utf-8'));

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

    public static function captureNormal($number, $message, $file, $line) {
        if (DEBUG == 1) {
            $error = ['type' => $number, 'message' => $message, 'file' => $file, 'line' => $line];
            if (Tpl::$render) {
                Tpl::error($error);
            } else {
                print_r($error);
            }
        }
    }

    public static function captureException($exception) {
        @ob_end_clean();
        if (DEBUG == 1) {
            header('Content-type:text/plain;');
            print_r($exception);
        } else {
            echo 'Runtime Error';
        }
    }

    public static function captureShutdown() {
        $error = error_get_last();
        if ($error) {
            @ob_end_clean();
            if (DEBUG == 1) {
                header('Content-type:text/plain;');
                print_r($error);
            } else {
                echo 'Runtime Error';
            }
        } else {
            return true;
        }
    }

    /**
     * 分发请求到对应的回调函数
     *
     * @param string $path 请求的路径
     */
    public function dispatch($path) {
        foreach ($this->_calls as $v) {
            $matches = [];
            if (preg_match($v['reg'], $path, $matches) === 1) {
                array_shift($matches);
                call_user_func_array($v['callback'], $matches);
                return;
            }
        }
        $controler = new Controler();
        $controler->show_404();
    }

}
