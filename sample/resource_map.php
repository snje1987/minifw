<?php

$value = array();
$value['/www/theme/default/script'] = array(
    'method' => 'uglify',
    'type' => 'dir',
    'dep' => '/theme/default/script',
);
$value['/www/theme/default/style/common.js'] = array(
    'method' => 'cssmin',
    'type' => 'file',
    'dep' => '/theme/default/style/common.js',
);
return $value;
