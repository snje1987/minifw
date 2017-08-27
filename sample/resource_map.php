<?php
$value = [];
$value['/www/theme/default/script'] = [
    'method' => 'uglify',
    'type' => 'dir',
    'dep' => '/theme/default/script',
];
$value['/www/theme/default/style/common.js'] = [
    'method' => 'cssmin',
    'type' => 'file',
    'dep' => '/theme/default/style/common.js',
];
return $value;
