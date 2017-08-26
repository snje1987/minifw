<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableDel extends FW\Table {

    const TBNAME = 'table_with_all';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    ];
    const FIELD = [
        'intfield' => ['type' => 'int(11)', 'comment' => 'A int field'],
    ];
    const INDEX = [
    ];
    const DIFF = [
        [
            'diff' => '-[1] `intfield` int(11) NOT NULL COMMENT "A int field"' . "\n" . '+[0] `intfield` int(11) NOT NULL COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT "A int field" first;',
        ],
        [
            'diff' => '- `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `id`;',
        ],
        [
            'diff' => '- `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `charfield`;',
        ],
        [
            'diff' => '- `textfield` text NOT NULL COMMENT "A text field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `textfield`;',
        ],
        [
            'diff' => '- `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `intfield_def`;',
        ],
        [
            'diff' => '- `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` DROP `charfield_def`;',
        ],
        [
            'diff' => '- PRIMARY KEY (`id`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY;',
        ],
        [
            'diff' => '- UNIQUE `uniqueindex` (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`;',
        ],
        [
            'diff' => '- INDEX `charfield` (`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`;',
        ],
        [
            'diff' => '- INDEX `intfield` (`intfield`,`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`;',
        ],
    ];

}
