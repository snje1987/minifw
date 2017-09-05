<?php
$value = array();
$value[] = array(
    'from' => '/tests/Resource/from/file1.js',
    'to' => '/tests/Resource/to/uglify/file1.js',
    'method' => 'uglify',
    'type' => 'file',
);
$value[] = array(
    'from' => '/tests/Resource/from/file1.css',
    'to' => '/tests/Resource/to/cssmin/file1.css',
    'method' => 'cssmin',
    'type' => 'file',
);
$value[] = array(
    'from' => '/tests/Resource/from/file1.css',
    'to' => '/tests/Resource/to/copy/file1.css',
    'method' => 'copy',
    'type' => 'file',
);

$value[] = array(
    'from' => '/tests/Resource/from/dir1',
    'to' => '/tests/Resource/to/uglify/dir1',
    'method' => 'uglify',
    'type' => 'dir',
    'tail' => '.js',
);
$value[] = array(
    'from' => '/tests/Resource/from/dir1',
    'to' => '/tests/Resource/to/cssmin/dir1',
    'method' => 'cssmin',
    'type' => 'dir',
    'tail' => '.css',
);
return $value;
