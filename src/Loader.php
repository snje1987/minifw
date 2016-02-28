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
 * @filename Loader.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-11 16:26:36
 * @version 1.0
 * @Description 负责加载类
 */

namespace Org\Snje\Minifw;
use Org\Snje\Minifw as Minifw;

/**
 * 用于动态加载类
 */
class Loader{

    public static $len;

    /**
     * 注册加载函数
     */
    public static function register(){
        //设置类的加载器
        spl_autoload_register([__NAMESPACE__ . '\Loader', 'class_loader']);
    }

    /**
     * 加载指定的类
     *
     * @param string $name 要加载的类的完全限定名
     * @return bool 成功返回true，否则返回false
     */
    public static function class_loader($name){
        if(strncmp(__NAMESPACE__ . '\\', $name, self::$len) !== 0){
            return false;
        }
        $name = substr($name, self::$len);
        $file_path = __DIR__ . '/' . str_replace('\\', '/', $name) . '.php';
        if(file_exists($file_path) && is_readable($file_path)){
            include($file_path);
            return true;
        }
        return false;
    }
}

Minifw\Loader::register();
Minifw\Loader::$len = strlen(__NAMESPACE__ . '\\');