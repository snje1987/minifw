<?php

$cfg['path'] = [
    'theme' => '/theme', //template path
    'res' => '/www', //resource path
    'compiled' => '/compiled', //compiled template
    'web_root' => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '', //web site's root
];

$cfg['fonts'] = [
    'secode' => [],
];

$cfg['main'] = [
    'db' => '', //Mysqli or SQLite (not tested)
    'timezone' => 'PRC',
    'dbprefix' => '',
    'encoding' => 'utf-8',
    'session' => 'session_id',
    'domain' => '',
    'uri' => isset($_SERVER['REQUEST_URI']) ? strval($_SERVER['REQUEST_URI']) : '',
    'cache' => 3600
];

if ($cfg['main']['uri'] === '' && isset($_GET['uri'])) {
    $cfg['main']['uri'] = strval($_GET['uri']);
}

$cfg['debug'] = [
    'debug' => 0,
    'tpl_always_compile' => 0,
];

$cfg['mysql'] = [
    'host' => 'localhost',
    'username' => '',
    'password' => '',
    'dbname' => '',
    'encoding' => 'utf8',
];

$cfg['sqlite'] = [
//    'path' => '/web.db'
];

$cfg['save'] = [
//    'html' => '/html',
];

$cfg['upload'] = [
//    'attach' => [
//        'path' => '/attach',
//        'allow' => ['jpg', 'svg', 'gif', 'png', 'tif'],
//    ],
//    'upload' => [
//        'path' => '/www/upload',
//        'allow' => ['jpg', 'svg', 'gif', 'png', 'tif'],
//    ],
];
