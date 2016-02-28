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
 * @filename System.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-12 11:11:57
 * @Description 系统的相关操作。
 */

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as Minifw;

/**
 * 系统的相关操作
 */
class System{

    protected $_calls = [];

    /**
     * 初始化系统
     */
    public function run(){
        $this->_set_env();
        $this->dispatch(
                $_SERVER[Minifw\Config::get('main', 'uri', 'REQUEST_URI')]);
    }

    public function reg_call($reg, $callback){
        $this->_calls[] = [
            'reg' => $reg,
            'callback' => $callback,
        ];
    }

    /**
     * 配置系统的主要参数
     */
    protected function _set_env(){
        //设置错误处理函数
        set_error_handler([__NAMESPACE__ . '\Error', 'captureNormal']);
        //设置异常处理函数
        set_exception_handler([__NAMESPACE__ . '\Error', 'captureException']);
        //设置停机处理函数
        register_shutdown_function([__NAMESPACE__ . '\Error', 'captureShutdown']);

        define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

        $_GET = self::magic_gpc($_GET);
        $_POST = self::magic_gpc($_POST);
        $_COOKIE = self::magic_gpc($_COOKIE);

        define('DEBUG', Minifw\Config::get('main', 'debug', 0));
        define('DBPREFIX', Minifw\Config::get('main', 'dbprefix', ''));
        date_default_timezone_set(Minifw\Config::get('main', 'timezone', 'UTC'));
        header('Content-type:text/html;charset=' . Minifw\Config::get('main', 'encoding', 'utf-8'));

        $session_name = Minifw\Config::get('main', 'session', 'PHPSESSION');
        session_name($session_name);
        session_set_cookie_params(36000, '/', Minifw\Config::get('main', 'domain', ''));

        //处理Flash丢失cookie的问题
        $session_id = '';
        isset($_POST[$session_name]) && $session_id = strval($_POST[$session_name]);
        if($session_id == ''){
            isset($_GET[$session_name]) && $session_id = strval($_GET[$session_name]);
        }
        if($session_id != ''){
            session_id($session_id);
        }
        session_start();
    }

    /**
     * 处理用户发送的数据，执行trim和去除多余的转义
     *
     * @param mixed $string 要处理的数据
     * @return mixed 处理后的数据
     */
    public static function magic_gpc($string){
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::magic_gpc($val);
            }
        } else {
            if(MAGIC_QUOTES_GPC){
                $string = stripslashes(trim($string));
            }
            else{
                $string = trim($string);
            }
        }
        return $string;
    }

    /**
     * 分发请求到对应的响应控制器
     *
     * @param string $path 请求的路径
     */
    public function dispatch($path){
        $order = Minifw\Config::get('route');
        foreach($order as $v){
            switch($v){
            case 'tpl':
                if($this->tpl($path)){
                    return;
                }
                break;
            case 'call':
                if($this->call($path)){
                    return;
                }
                break;
            }
        }
        Minifw\Server::show_404();
    }

    /**
     * 处理回调函数
     *
     * @param string $path 请求的路径
     * @return boolean 成功返回true,失败返回false
     */
    public function call($path){
        foreach($this->_calls as $v){
            $matches = [];
            if(preg_match($v['reg'], $path, $matches) === 1){
                array_shift($matches);
                call_user_func_array($v['callback'], $matches);
                return true;
            }
        }
        return false;
    }

    /**
     * 查找并显示模板
     *
     * @param string $path 请求的路径
     * @return boolean 成功返回true,失败返回false
     */
    public function tpl($path){
        list($dir, $fname, $args) = self::path_info($path);
        $theme = Config::get('main', 'tpl_name', '');
        if($fname == ''){
            $fname = Config::get('main', 'def_tpl', '');
        }

        if($theme == '' || $fname == ''){
            return false;
        }

        $tpl = $dir . '/' . $fname;
        return Tpl::display($tpl, $args, $theme);
    }

    /**
     * 分析请求，找到对应的模板
     *
     * @param string $path 请求的路径
     * @return array 请求的响应控制器、响应方法以及参数
     */
    protected static function path_info($path){
        $path = strval($path);
        $index = strpos($path,'?');
        if($index !== false){
            $path = substr($path,0,$index);
        }

        $matches = [];
        if(preg_match('/^(\/[_a-z0-9\/]*)?\/([_a-z\.0-9]*)(-([_a-z0-9-]*))?(!.*)?$/', $path, $matches) == 0){
            Server::show_404();
        }

        $dir = $matches[1];
        $fname = $matches[2];
        $args = [];
        if(isset($matches[4])){
            $args = explode('-', $matches[4]);
        }

        return [$dir, $fname, $args];
    }

}
