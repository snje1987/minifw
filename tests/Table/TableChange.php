<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableChange extends FW\Table {

    public static $tbname = 'table_with_all';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'MyISAM',
        'charset' => 'GBK',
        'comment' => 'Table To Change',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'comment' => 'ID'),
        'id2' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID2'),
        'intfield' => array('type' => 'int(10) unsigned', 'comment' => 'A int field'),
        'charfield' => array('type' => 'varchar(100)', 'default' => '#', 'comment' => 'A varchar field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field change'),
        'intfield_def' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'charfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A varchar field'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('intfield')),
        'intfield' => array('fields' => array('charfield')),
        'charfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('fields' => array('intfield'))
    );
    public static $diff = array(
        array(
            'diff' => '- Engine=InnoDB' . "\n" . '+ Engine=MyISAM',
            'trans' => 'ALTER TABLE `table_with_all` ENGINE=MyISAM;',
        ),
        array(
            'diff' => '- Comment=\'Table To Create\'' . "\n" . '+ Comment=\'Table To Change\'',
            'trans' => 'ALTER TABLE `table_with_all` COMMENT=\'Table To Change\';',
        ),
        array(
            'diff' => '- Charset=\'utf8\'' . "\n" . '+ Charset=\'GBK\'',
            'trans' => 'ALTER TABLE `table_with_all` DEFAULT CHARSET=\'GBK\';',
        ),
        array(
            'diff' => '+[1] `id2` int(10) unsigned NOT NULL COMMENT \'ID2\'',
            'trans' => 'ALTER TABLE `table_with_all` ADD `id2` int(10) unsigned NOT NULL COMMENT \'ID2\' after `id`;',
        ),
        array(
            'diff' => '-[0] `id` int(10) unsigned NOT NULL auto_increment COMMENT \'ID\'' . "\n" . '+[0] `id` int(10) unsigned NOT NULL COMMENT \'ID\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `id` `id` int(10) unsigned NOT NULL COMMENT \'ID\' first;',
        ),
        array(
            'diff' => '-[2] `intfield` int(11) NOT NULL COMMENT \'A int field\'' . "\n" . '+[2] `intfield` int(10) unsigned NOT NULL COMMENT \'A int field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(10) unsigned NOT NULL COMMENT \'A int field\' after `id2`;',
        ),
        array(
            'diff' => '-[3] `charfield` varchar(200) NOT NULL COMMENT \'A varchar field\'' . "\n" . '+[3] `charfield` varchar(100) NOT NULL DEFAULT \'#\' COMMENT \'A varchar field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(100) NOT NULL DEFAULT \'#\' COMMENT \'A varchar field\' after `intfield`;',
        ),
        array(
            'diff' => '-[4] `textfield` text NOT NULL COMMENT \'A text field\'' . "\n" . '+[4] `textfield` text NOT NULL COMMENT \'A text field change\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT \'A text field change\' after `charfield`;',
        ),
        array(
            'diff' => '-[5] `intfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A int field\'' . "\n" . '+[5] `intfield_def` int(11) NOT NULL COMMENT \'A int field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL COMMENT \'A int field\' after `textfield`;',
        ),
        array(
            'diff' => '-[6] `charfield_def` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'A varchar field\'' . "\n" . '+[6] `charfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A varchar field\'',
 'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A varchar field\' after `intfield_def`;',
        ),
        array(
            'diff' => '- PRIMARY KEY (`id`) COMMENT \'主键\'' . "\n" . '+ PRIMARY KEY (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY, ADD PRIMARY KEY (`intfield`);',
        ),
        array(
            'diff' => '- INDEX `intfield` (`intfield`,`charfield`)' . "\n" . '+ INDEX `intfield` (`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`, ADD INDEX `intfield` (`charfield`);',
        ),
        array(
            'diff' => '- INDEX `charfield` (`charfield`)' . "\n" . '+ INDEX `charfield` (`intfield`,`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`, ADD INDEX `charfield` (`intfield`,`charfield`);',
        ),
        array(
            'diff' => '- UNIQUE `uniqueindex` (`intfield`)' . "\n" . '+ INDEX `uniqueindex` (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`, ADD INDEX `uniqueindex` (`intfield`);',
        ),
        array(
            'diff' => '-[1] `id2` int(10) unsigned NOT NULL COMMENT \'ID2\'' . "\n" . '+[1] `id2` int(10) unsigned NOT NULL auto_increment COMMENT \'ID2\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `id2` `id2` int(10) unsigned NOT NULL auto_increment COMMENT \'ID2\' after `id`;',
        ),
    );

}
