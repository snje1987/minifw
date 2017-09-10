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
    'from' => array(
        '/tests/Resource/from/dir2/file4.css',
        '/tests/Resource/from/dir2/file4.js',
    ),
    'to' => array(
        '/tests/Resource/to/copy/dir2/file4.css',
        '/tests/Resource/to/copy/dir2/file4.js'
    ),
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
$value[] = array(
    'from' => '/tests/Resource/from/dir1',
    'to' => '/tests/Resource/to/copy/dir1',
    'method' => 'copy',
    'type' => 'dir',
    'tail' => '.js',
);
$value[] = array(
    'from' => array(
        '/tests/Resource/from/dir1',
        '/tests/Resource/from/dir2',
    ),
    'to' => array(
        '/tests/Resource/to/copy/mdir/dir1',
        '/tests/Resource/to/copy/mdir/dir2',
    ),
    'method' => 'copy',
    'type' => 'dir',
    'tail' => '.js',
);
return $value;
