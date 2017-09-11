<?php

$value = array();
$value[] = array(
    'method' => 'uglify',
    'type' => 'dir',
    'tail' => '.js',
    'map' => array(
        '/www/theme/default/script/' => '/theme/default/script/',
    ),
);
$value[] = array(
    'method' => 'cssmin',
    'type' => 'file',
    'map' => array(
        '/www/theme/default/style/common.css' => '/theme/default/style/common.css',
    ),
);
return $value;
