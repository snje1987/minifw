<?php

$value = array();

$value[] = array(
    'from' => '/theme/default/script',
    'to' => '/www/theme/default/script',
    'method' => 'uglify',
    'type' => 'dir',
    'tail' => '.js',
);
$value[] = array(
    'from' => '/theme/default/style/common.css',
    'to' => '/www/theme/default/style/common.css',
    'method' => 'cssmin',
    'type' => 'file',
);
return $value;
