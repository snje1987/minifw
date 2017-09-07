<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableDel extends FW\Table {

    public static $tbname = 'table_with_all';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    );
    public static $field = array(
        'intfield' => array('type' => 'int(11)', 'comment' => 'A int field'),
    );
    public static $index = array(
    );
    public static $diff = array(
        array(
            'diff' => '- `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `id`;',
        ),
        array(
            'diff' => '- `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `charfield`;',
        ),
        array(
            'diff' => '- `textfield` text NOT NULL COMMENT "A text field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `textfield`;',
        ),
        array(
            'diff' => '- `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `intfield_def`;',
        ),
        array(
            'diff' => '- `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `charfield_def`;',
        ),
        array(
            'diff' => '- PRIMARY KEY (`id`) COMMENT "主键"',
            'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY;',
        ),
        array(
            'diff' => '- UNIQUE `uniqueindex` (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`;',
        ),
        array(
            'diff' => '- INDEX `charfield` (`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`;',
        ),
        array(
            'diff' => '- INDEX `intfield` (`intfield`,`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`;',
        ),
    );

}
