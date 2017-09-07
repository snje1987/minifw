<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableChange extends FW\Table {

    const TBNAME = 'table_with_all';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'MyISAM',
        'charset' => 'GBK',
        'comment' => 'Table To Change',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'comment' => 'ID'],
        'intfield' => ['type' => 'int(10) unsigned', 'comment' => 'A int field'],
        'charfield' => ['type' => 'varchar(100)', 'default' => '#', 'comment' => 'A varchar field'],
        'textfield' => ['type' => 'text', 'comment' => 'A text field change'],
        'intfield_def' => ['type' => 'int(11)', 'comment' => 'A int field'],
        'charfield_def' => ['type' => 'int(11)', 'default' => '0', 'comment' => 'A varchar field'],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['intfield']],
        'intfield' => ['fields' => ['charfield']],
        'charfield' => ['fields' => ['intfield', 'charfield']],
        'uniqueindex' => ['fields' => ['intfield']]
    ];
    const DIFF = [
        [
            'diff' => '- Engine=InnoDB' . "\n" . '+ Engine=MyISAM',
            'trans' => 'ALTER TABLE `table_with_all` ENGINE=MyISAM;',
        ],
        [
            'diff' => '- Comment="Table To Create"' . "\n" . '+ Comment="Table To Change"',
            'trans' => 'ALTER TABLE `table_with_all` COMMENT="Table To Change";',
        ],
        [
            'diff' => '- Charset="utf8"' . "\n" . '+ Charset="GBK"',
            'trans' => 'ALTER TABLE `table_with_all` DEFAULT CHARSET="GBK";',
        ],
        [
            'diff' => '-[0] `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"' . "\n" . '+[0] `id` int(10) unsigned NOT NULL COMMENT "ID"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `id` `id` int(10) unsigned NOT NULL COMMENT "ID" first;',
        ],
        [
            'diff' => '-[1] `intfield` int(11) NOT NULL COMMENT "A int field"' . "\n" . '+[1] `intfield` int(10) unsigned NOT NULL COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(10) unsigned NOT NULL COMMENT "A int field" after `id`;',
        ],
        [
            'diff' => '-[2] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"' . "\n" . '+[2] `charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field" after `intfield`;',
        ],
        [
            'diff' => '-[3] `textfield` text NOT NULL COMMENT "A text field"' . "\n" . '+[3] `textfield` text NOT NULL COMMENT "A text field change"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT "A text field change" after `charfield`;',
        ],
        [
            'diff' => '-[4] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"' . "\n" . '+[4] `intfield_def` int(11) NOT NULL COMMENT "A int field"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL COMMENT "A int field" after `textfield`;',
        ],
        [
            'diff' => '-[5] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"' . "\n" . '+[5] `charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field"',
            'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field" after `intfield_def`;',
        ],
        [
            'diff' => '- PRIMARY KEY (`id`) COMMENT "主键"' . "\n" . '+ PRIMARY KEY (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY, ADD PRIMARY KEY (`intfield`);',
        ],
        [
            'diff' => '- INDEX `intfield` (`intfield`,`charfield`)' . "\n" . '+ INDEX `intfield` (`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`, ADD INDEX `intfield` (`charfield`);',
        ],
        [
            'diff' => '- INDEX `charfield` (`charfield`)' . "\n" . '+ INDEX `charfield` (`intfield`,`charfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`, ADD INDEX `charfield` (`intfield`,`charfield`);',
        ],
        [
            'diff' => '- UNIQUE `uniqueindex` (`intfield`)' . "\n" . '+ INDEX `uniqueindex` (`intfield`)',
            'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`, ADD INDEX `uniqueindex` (`intfield`);',
        ],
    ];

}
