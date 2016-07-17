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

    /**
     * @covers Org\Snje\Minifw\Table::table_diff
     */
    public function test_add() {
        $table = TableAdd::get();
        $diff = $table->table_diff();
        $this->assertEquals([
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[0]&nbsp;`id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `id` int(10) unsigned NOT NULL auto_increment COMMENT "ID" first;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[2]&nbsp;`charfield` varchar(200) NOT NULL COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `charfield` varchar(200) NOT NULL COMMENT "A varchar field" after `intfield`;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[3]&nbsp;`textfield` text NOT NULL COMMENT "A text field"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `textfield` text NOT NULL COMMENT "A text field" after `charfield`;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[4]&nbsp;`addfield` text NOT NULL COMMENT "A add field"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `addfield` text NOT NULL COMMENT "A add field" after `textfield`;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[5]&nbsp;`intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field" after `addfield`;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+[6]&nbsp;`charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field" after `intfield_def`;',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+&nbsp;PRIMARY KEY (`id`)</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD PRIMARY KEY (`id`);',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+&nbsp;INDEX `intfield` (`intfield`,`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `intfield` (`intfield`,`charfield`);',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+&nbsp;UNIQUE `uniqueindex` (`intfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD UNIQUE `uniqueindex` (`intfield`);',
            ],
            [
                'table' => 'table_with_one',
                'diff' => '<p class="green">+&nbsp;INDEX `addfield` (`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_one` ADD INDEX `addfield` (`charfield`);',
            ],
                ], $diff);
    }

    /**
     * @covers Org\Snje\Minifw\Table::table_diff
     */
    public function test_del() {
        $table = TableDel::get();
        $diff = $table->table_diff();
        $this->assertEquals([
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[1]&nbsp;`intfield` int(11) NOT NULL COMMENT "A int field"</p><p class = "green">+[0]&nbsp;`intfield` int(11) NOT NULL COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT "A int field" first;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;`id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP `id`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;`charfield` varchar(200) NOT NULL COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP `charfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;`textfield` text NOT NULL COMMENT "A text field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP `textfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;`intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP `intfield_def`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;`charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP `charfield_def`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;PRIMARY KEY (`id`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;UNIQUE `uniqueindex` (`intfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;INDEX `charfield` (`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;INDEX `intfield` (`intfield`,`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`;',
            ],
                ], $diff);
    }

    /**
     * @covers Org\Snje\Minifw\Table::table_diff
     */
    public function test_change() {
        $table = TableChange::get();
        $diff = $table->table_diff();
        $this->assertEquals([
            [
                'table' => 'table_with_all',
                'diff' => '<p class = "red">-&nbsp;Engine=InnoDB</p><p class = "green">+&nbsp;Engine=MyISAM</p>',
                'trans' => 'ALTER TABLE `table_with_all` ENGINE=MyISAM;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class = "red">-&nbsp;Comment="Table To Create"</p><p class = "green">+&nbsp;Comment="Table To Change"</p>',
                'trans' => 'ALTER TABLE `table_with_all` COMMENT="Table To Change";',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[0]&nbsp;`id` int(10) unsigned NOT NULL auto_increment COMMENT "ID"</p><p class = "green">+[0]&nbsp;`id` int(10) unsigned NOT NULL COMMENT "ID"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `id` `id` int(10) unsigned NOT NULL COMMENT "ID" first;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[1]&nbsp;`intfield` int(11) NOT NULL COMMENT "A int field"</p><p class = "green">+[1]&nbsp;`intfield` int(10) unsigned NOT NULL COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(10) unsigned NOT NULL COMMENT "A int field" after `id`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[2]&nbsp;`charfield` varchar(200) NOT NULL COMMENT "A varchar field"</p><p class = "green">+[2]&nbsp;`charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(100) NOT NULL DEFAULT "#" COMMENT "A varchar field" after `intfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[3]&nbsp;`textfield` text NOT NULL COMMENT "A text field"</p><p class = "green">+[3]&nbsp;`textfield` text NOT NULL COMMENT "A text field change"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT "A text field change" after `charfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[4]&nbsp;`intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"</p><p class = "green">+[4]&nbsp;`intfield_def` int(11) NOT NULL COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL COMMENT "A int field" after `textfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"</p><p class = "green">+[5]&nbsp;`charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A varchar field" after `intfield_def`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;PRIMARY KEY (`id`)</p><p class="green">+&nbsp;PRIMARY KEY (`intfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP PRIMARY KEY, ADD PRIMARY KEY (`intfield`);',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;INDEX `intfield` (`intfield`,`charfield`)</p><p class="green">+&nbsp;INDEX `intfield` (`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `intfield`, ADD INDEX `intfield` (`charfield`);',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;INDEX `charfield` (`charfield`)</p><p class="green">+&nbsp;INDEX `charfield` (`intfield`,`charfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `charfield`, ADD INDEX `charfield` (`intfield`,`charfield`);',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-&nbsp;UNIQUE `uniqueindex` (`intfield`)</p><p class="green">+&nbsp;INDEX `uniqueindex` (`intfield`)</p>',
                'trans' => 'ALTER TABLE `table_with_all` DROP INDEX `uniqueindex`, ADD INDEX `uniqueindex` (`intfield`);',
            ],
                ], $diff);
    }

    /**
     * @covers Org\Snje\Minifw\Table::table_diff
     */
    public function test_move() {
        $table = TableMove::get();
        $diff = $table->table_diff();
        $this->assertEquals([
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"</p><p class = "green">+[0]&nbsp;`charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield_def` `charfield_def` varchar(200) NOT NULL DEFAULT "" COMMENT "A varchar field" first;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"</p><p class = "green">+[1]&nbsp;`intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield_def` `intfield_def` int(11) NOT NULL DEFAULT "0" COMMENT "A int field" after `charfield_def`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`textfield` text NOT NULL COMMENT "A text field"</p><p class = "green">+[2]&nbsp;`textfield` text NOT NULL COMMENT "A text field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `textfield` `textfield` text NOT NULL COMMENT "A text field" after `intfield_def`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`charfield` varchar(200) NOT NULL COMMENT "A varchar field"</p><p class = "green">+[3]&nbsp;`charfield` varchar(200) NOT NULL COMMENT "A varchar field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `charfield` `charfield` varchar(200) NOT NULL COMMENT "A varchar field" after `textfield`;',
            ],
            [
                'table' => 'table_with_all',
                'diff' => '<p class="red">-[5]&nbsp;`intfield` int(11) NOT NULL COMMENT "A int field"</p><p class = "green">+[4]&nbsp;`intfield` int(11) NOT NULL COMMENT "A int field"</p>',
                'trans' => 'ALTER TABLE `table_with_all` CHANGE `intfield` `intfield` int(11) NOT NULL COMMENT "A int field" after `charfield`;',
            ],
                ], $diff);
    }

}
