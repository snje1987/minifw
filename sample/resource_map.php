<?php

$value = array();
$value[] = array(
    'method' => 'js',
    'type' => 'dir',
    'tail' => '.js',
    'map' => array(
        '/www/theme/default/script/' => '/theme/default/script/',
    ),
);
$value[] = array(
    'method' => 'css',
    'type' => 'file',
    'map' => array(
        '/www/theme/default/style/common.css' => '/theme/default/style/common.css',
    ),
);
return $value;
