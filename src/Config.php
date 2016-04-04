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
 * @filename Config.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-12 10:19:40
 * @version 1.0
 * @Description 网站的配置信息的加载与保存
 */

namespace Org\Snje\Minifw;

/**
 * 用于加载、缓存和保存配置数据
 */
class Config{

    /**
     * @var array 缓存加载过的数据
     */
    protected static $_data;

    /**
     * 获取指定的配数据
     *
     * @param string $key 要获取的配置段
     * @param string $name 要获取的键名
     * @param mixed $default 键不存在时返回的默认值
     * @return mixed 如果配置段不存在,返回false;如果存在,那么在键名为空时返回整个段，
     * 在键名不为空时，如果段中存在键则返回相应键的值，否则返回默认值
     */
    public static function get($key, $name = '', $default = false){
        if($key == '' || !isset(self::$_data[$key])){
            return false;
        }
        if($name == ''){
            return self::$_data[$key];
        }
        if(!isset(self::$_data[$key][$name])){
            return $default;
        }
        return self::$_data[$key][$name];
    }

    /**
     * 加载相应的配置文件
     * @param arrsy $files 要加载的配置文件列表
     * @return array 如果文件存在则返回文件内容，否则返回空数组
     */
    public static function load_config($files = []){
        $cfg = [];
        require_once __DIR__ . '/defaults.php';
        foreach($files as $file){
            require_once WEB_ROOT . $file;
        }
        self::$_data = $cfg;
    }

}
