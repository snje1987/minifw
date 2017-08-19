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
 * Default config of the framework. DO NOT MODIFY!!
 */
$cfg['path'] = array(
    'theme' => '/theme', //template path
    'res' => '/www', //resource path
    'compiled' => '/compiled', //compiled template
    'web_root' => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '', //web site's root
);

$cfg['fonts'] = array(
    'secode' => array(),
);

$cfg['main'] = array(
    'db' => '', //Mysqli or SQLite (not tested)
    'timezone' => 'PRC',
    'dbprefix' => '',
    'encoding' => 'utf-8',
    'session' => 'session_id',
    'domain' => '',
    'err_404' => '/www/error/404.html',
    'uri' => isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '',
    'theme' => '',
    'cache' => 3600
);

if ($cfg['main']['uri'] === '' && isset($_GET['uri'])) {
    $cfg['main']['uri'] = strval($_GET['uri']);
}

$cfg['debug'] = array(
    'debug' => 0,
    'tpl_always_compile' => 0,
);

$cfg['mysql'] = array(
    'host' => 'localhost',
    'username' => '',
    'password' => '',
    'dbname' => '',
    'encoding' => 'utf8',
);

$cfg['sqlite'] = array(
//    'path' => '/web.db'
);

$cfg['save'] = array(
//    'html' => '/html',
);

$cfg['upload'] = array(
//    'attach' => array(
//        'path' => '/attach',
//        'allow' => array('jpg', 'svg', 'gif', 'png', 'tif'),
//    ),
//    'upload' => array(
//        'path' => '/www/upload',
//        'allow' => array('jpg', 'svg', 'gif', 'png', 'tif'),
//    ],
);
