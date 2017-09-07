<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableAdd extends FW\Table {

    public static $tbname = 'table_with_one';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'),
        'intfield' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'charfield' => array('type' => 'varchar(200)', 'comment' => 'A varchar field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field'),
        'addfield' => array('type' => 'text', 'comment' => 'A add field'),
        'intfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'),
        'charfield_def' => array('type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id'), 'comment' => '主键'),
        'intfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('unique' => true, 'fields' => array('intfield')),
        'addfield' => array('fields' => array('charfield')),
    );
    public static $diff = array(
        array(
            'diff' => '+[0] `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID" first;',
        ),
        array(
            'diff' => '+[2] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `charfield` varchar(200) NOT NULL COMMENT "A varchar field" after `intfield`;',
        ),
        array(
            'diff' => '+[3] `textfield` text NOT NULL COMMENT "A text field"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `textfield` text NOT NULL COMMENT "A text field" after `charfield`;',
        ),
        array(
            'diff' => '+[4] `addfield` text NOT NULL COMMENT "A add field"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `addfield` text NOT NULL COMMENT "A add field" after `textfield`;',
        ),
        array(
            'diff' => '+[5] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field" after `addfield`;',
        ),
        array(
            'diff' => '+[6] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_one` ADD `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field" after `intfield_def`;',
        ),
        array(
            'diff' => '+ PRIMARY KEY (`id`) COMMENT "主键"',
            'trans' => 'ALTER TABLE `table_with_one` ADD PRIMARY KEY (`id`) COMMENT "主键";',
        ),
        array(
            'diff' => '+ INDEX `intfield` (`intfield`,`charfield`)',
            'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `intfield` (`intfield`,`charfield`);',
        ),
        array(
            'diff' => '+ UNIQUE `uniqueindex` (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_one` ADD UNIQUE `uniqueindex` (`intfield`);',
        ),
        array(
            'diff' => '+ INDEX `addfield` (`charfield`)',
            'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `addfield` (`charfield`);',
        ),
    );

}
