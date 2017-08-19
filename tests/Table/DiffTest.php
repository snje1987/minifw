<?php

/*
 * Copyright (C) 2016 Yang Ming <yangming0116@163.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

/**
 * Description of ManageTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class DiffTest extends Ts\TestCommon {

    public function test_add() {
        $table = TableAdd::get();
        $diff = $table->table_diff();
        $this->assertEquals(array(
            array(
                'table' => 'table_with_one',
                'diff' => '+[0] `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID" first;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+[2] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `charfield` varchar(200) NOT NULL COMMENT "A varchar field" after `intfield`;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+[3] `textfield` text NOT NULL COMMENT "A text field"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `textfield` text NOT NULL COMMENT "A text field" after `charfield`;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+[4] `addfield` text NOT NULL COMMENT "A add field"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `addfield` text NOT NULL COMMENT "A add field" after `textfield`;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+[5] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field" after `addfield`;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+[6] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_one` ADD `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field" after `intfield_def`;',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+ PRIMARY KEY (`id`)',
                'trans' => 'ALTER TABLE `table_with_one` ADD PRIMARY KEY (`id`);',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+ INDEX `intfield` (`intfield`,`charfield`)',
                'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `intfield` (`intfield`,`charfield`);',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+ UNIQUE `uniqueindex` (`intfield`)',
                'trans' => 'ALTER TABLE `table_with_one` ADD UNIQUE `uniqueindex` (`intfield`);',
            ),
            array(
                'table' => 'table_with_one',
                'diff' => '+ INDEX `addfield` (`charfield`)',
                'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `addfield` (`charfield`);',
            ),
                ), $diff);
    }

    public function test_del() {
        $table = TableDel::get();
        $diff = $table->table_diff();
        $this->assertEquals(array(
            array(
                'table' => 'table_with_all',
                'diff' => '-[1] `intfield` int(11) NOT NULL COMMENT "A int field"' . "\n" . '+[0] `intfield` int(11) NOT NULL COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT "A int field" first;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"',
                'trans' => 'ALTER TABLE `table_with_all` DROP `id`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` DROP `charfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- `textfield` text NOT NULL COMMENT "A text field"',
                'trans' => 'ALTER TABLE `table_with_all` DROP `textfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` DROP `intfield_def`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` DROP `charfield_def`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- PRIMARY KEY (`id`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY;',
           ),
            array(
                'table' => 'table_with_all',
                'diff' => '- UNIQUE `uniqueindex` (`intfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- INDEX `charfield` (`charfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- INDEX `intfield` (`intfield`,`charfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`;',
            ),
                ), $diff);
    }

    public function test_change() {
        $table = TableChange::get();
        $diff = $table->table_diff();
        $this->assertEquals(array(
            array(
                'table' => 'table_with_all',
                'diff' => '- Engine=InnoDB' . "\n" . '+ Engine=MyISAM',
                'trans' => 'ALTER TABLE `table_with_all` ENGINE=MyISAM;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- Comment="Table To Create"' . "\n" . '+ Comment="Table To Change"',
                'trans' => 'ALTER TABLE `table_with_all` COMMENT="Table To Change";',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- Charset="utf8"' . "\n" . '+ Charset="GBK"',
                'trans' => 'ALTER TABLE `table_with_all` DEFAULT CHARSET="GBK";',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[0] `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"' . "\n" . '+[0] `id` int(10) unsigned NOT NULL COMMENT "ID"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `id` `id` int(10) unsigned NOT NULL COMMENT "ID" first;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[1] `intfield` int(11) NOT NULL COMMENT "A int field"' . "\n" . '+[1] `intfield` int(10) unsigned NOT NULL COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(10) unsigned NOT NULL COMMENT "A int field" after `id`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[2] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"' . "\n" . '+[2] `charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field" after `intfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[3] `textfield` text NOT NULL COMMENT "A text field"' . "\n" . '+[3] `textfield` text NOT NULL COMMENT "A text field change"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT "A text field change" after `charfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[4] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"' . "\n" . '+[4] `intfield_def` int(11) NOT NULL COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL COMMENT "A int field" after `textfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"' . "\n" . '+[5] `charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field" after `intfield_def`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- PRIMARY KEY (`id`)' . "\n" . '+ PRIMARY KEY (`intfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY, ADD PRIMARY KEY (`intfield`);',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- INDEX `intfield` (`intfield`,`charfield`)' . "\n" . '+ INDEX `intfield` (`charfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`, ADD INDEX `intfield` (`charfield`);',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- INDEX `charfield` (`charfield`)' . "\n" . '+ INDEX `charfield` (`intfield`,`charfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`, ADD INDEX `charfield` (`intfield`,`charfield`);',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '- UNIQUE `uniqueindex` (`intfield`)' . "\n" . '+ INDEX `uniqueindex` (`intfield`)',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`, ADD INDEX `uniqueindex` (`intfield`);',
            ),
                ), $diff);
    }

    public function test_move() {
        $table = TableMove::get();
        $diff = $table->table_diff();
        $this->assertEquals(array(
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"' . "\n" . '+[0] `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field" first;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"' . "\n" . '+[1] `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field" after `charfield_def`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `textfield` text NOT NULL COMMENT "A text field"' . "\n" . '+[2] `textfield` text NOT NULL COMMENT "A text field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT "A text field" after `intfield_def`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"' . "\n" . '+[3] `charfield` varchar(200) NOT NULL COMMENT "A varchar field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(200) NOT NULL COMMENT "A varchar field" after `textfield`;',
            ),
            array(
                'table' => 'table_with_all',
                'diff' => '-[5] `intfield` int(11) NOT NULL COMMENT "A int field"' . "\n" . '+[4] `intfield` int(11) NOT NULL COMMENT "A int field"',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT "A int field" after `charfield`;',
            ),
                ), $diff);
    }

}
