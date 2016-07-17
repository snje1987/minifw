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
class CreateTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Table::create
     * @covers Org\Snje\Minifw\Table::drop
     */
    public function test_create() {
        $table_create = TableWithAll::get();
        $table_create->drop();
        $table_create->create();

        $db = \Org\Snje\Minifw\DB::get();

        $sql = 'show create table `' . $table_create::TBNAME . '`';
        $ret = $db->get_query($sql);
        $this->assertArrayHasKey(0, $ret);
        $ret = $ret[0];
        $leftsql = $ret['Create Table'];

        $rightsql = 'CREATE TABLE `table_with_all` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID\',
  `intfield` int(11) NOT NULL COMMENT \'A int field\',
  `charfield` varchar(200) NOT NULL COMMENT \'A varchar field\',
  `textfield` text NOT NULL COMMENT \'A text field\',
  `intfield_def` int(11) NOT NULL DEFAULT \'0\' COMMENT \'A int field\',
  `charfield_def` varchar(200) NOT NULL DEFAULT \'\' COMMENT \'A varchar field\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueindex` (`intfield`),
  KEY `charfield` (`charfield`),
  KEY `intfield` (`intfield`,`charfield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Table To Create\'';

        $this->assertEquals($rightsql, $leftsql);

        $table_create = TableWithOne::get();
        $table_create->drop();
        $table_create->create();

        $sql = 'show create table `' . $table_create::TBNAME . '`';
        $ret = $db->get_query($sql);
        $this->assertArrayHasKey(0, $ret);
        $ret = $ret[0];
        $leftsql = $ret['Create Table'];

        $rightsql = 'CREATE TABLE `table_with_one` (
  `intfield` int(11) NOT NULL COMMENT \'A int field\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Table To Create\'';

        $this->assertEquals($rightsql, $leftsql);
    }

}
