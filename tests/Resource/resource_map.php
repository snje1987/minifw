<?php

$value = array();
$value[] = array(
    'method' => 'js',
    'type' => 'file',
    'map' => array(
        '/tests/Resource/to/uglify/file1.js' => '/tests/Resource/from/file1.js',
    ),
);
$value[] = array(
    'method' => 'css',
    'type' => 'file',
    'map' => array(
        '/tests/Resource/to/cssmin/file1.css' => '/tests/Resource/from/file1.css',
    ),
);
$value[] = array(
    'method' => 'copy',
    'type' => 'file',
    'map' => array(
        '/tests/Resource/to/copy/file1.css' => '/tests/Resource/from/file1.css',
    ),
);
$value[] = array(
    'method' => 'copy',
    'type' => 'file',
    'map' => array(
        '/tests/Resource/to/copy/dir2/file4.js' => '/tests/Resource/from/dir2/file4.js',
        '/tests/Resource/to/copy/dir2/file4.css' => '/tests/Resource/from/dir2/file4.css',
    ),
);

$value[] = array(
    'method' => 'js',
    'type' => 'dir',
    'tail' => '.js',
    'map' => array(
        '/tests/Resource/to/uglify/dir1' => '/tests/Resource/from/dir1',
    ),
);
$value[] = array(
    'method' => 'css',
    'type' => 'dir',
    'tail' => '.css',
    'map' => array(
        '/tests/Resource/to/cssmin/dir1' => '/tests/Resource/from/dir1',
    ),
);
$value[] = array(
    'method' => 'copy',
    'type' => 'dir',
    'tail' => '.js',
    'map' => array(
        '/tests/Resource/to/copy/dir1' => '/tests/Resource/from/dir1',
    ),
);
$value[] = array(
    'method' => 'copy',
    'type' => 'dir',
    'tail' => '.js',
    'map' => array(
        '/tests/Resource/to/copy/mdir/dir2' => '/tests/Resource/from/dir2',
        '/tests/Resource/to/copy/mdir/dir1' => '/tests/Resource/from/dir1',
    ),
);
return $value;
