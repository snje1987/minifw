<?php

/*
 * Copyright (C] 2014 Yang Ming <yangming0116@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option] any later version.
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
 * @filename defaults.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @datetime 2014-10-18  22:55:03
 * @Description 网站的默认配置,不要修改此文件,请修改根目录下的config.php
 */
$cfg['path'] = [
    'theme' => '/theme', //网站的模板
    'res' => '/www', //资源文件位置
    'compiled' => '/compiled', //编译后的模板
    'ttfs' => '/ttfs', //字体文件
];

$cfg['main'] = [
    'db' => 'mysql',
    'timezone' => 'PRC',
    'dbprefix' => '',
    'encoding' => 'utf-8',
    'session' => 'session_id',
    'domain' => '',
    'err_404' => '/www/error/404.html',
    'uri' => isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '/',
    'theme' => '',
    'cache' => 3600
];

$cfg['debug'] = [
    'debug' => 0,
    'tpl_always_compile' => 0,
];

$cfg['mysql'] = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'minifw',
    'encoding' => 'utf8',
];

$cfg['sqlite'] = [
    'path' => '/web.db'
];

$cfg['save'] = [
    'html' => '/html',
];

$cfg['upload'] = [
    'attach' => [
        'path' => '/attach',
        'allow' => ['jpg', 'svg', 'gif', 'png', 'tif'],
    ],
    'upload' => [
        'path' => '/www/upload',
        'allow' => ['jpg', 'svg', 'gif', 'png', 'tif'],
    ],
];
