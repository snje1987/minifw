<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableMove extends FW\Table {

    public static $tbname = 'table_with_all';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    );
    public static $field = array(
        'charfield_def' => array('type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'),
        'intfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field'),
        'charfield' => array('type' => 'varchar(200)', 'comment' => 'A varchar field'),
        'intfield' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id'), 'comment' => '主键'),
        'charfield' => array('fields' => array('charfield')),
        'intfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('unique' => true, 'fields' => array('intfield'))
    );
    public static $diff = array(
        array(
            'diff' => '-[5] `charfield_def` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'A varchar field\'' . "\n" . '+[0] `charfield_def` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'A varchar field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'A varchar field\' first;',
        ),
        array(
            'diff' => '-[5] `intfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A int field\'' . "\n" . '+[1] `intfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A int field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A int field\' after `charfield_def`;',
        ),
        array(
            'diff' => '-[5] `textfield` text NOT NULL COMMENT \'A text field\'' . "\n" . '+[2] `textfield` text NOT NULL COMMENT \'A text field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT \'A text field\' after `intfield_def`;',
        ),
        array(
            'diff' => '-[5] `charfield` varchar(200) NOT NULL COMMENT \'A varchar field\'' . "\n" . '+[3] `charfield` varchar(200) NOT NULL COMMENT \'A varchar field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(200) NOT NULL COMMENT \'A varchar field\' after `textfield`;',
        ),
        array(
            'diff' => '-[5] `intfield` int(11) NOT NULL COMMENT \'A int field\'' . "\n" . '+[4] `intfield` int(11) NOT NULL COMMENT \'A int field\'',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT \'A int field\' after `charfield`;',
        ),
    );

}
