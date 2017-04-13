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
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class GetTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Table::get
     */
    public function test_get() {
        $table1 = TableWithAll::get();

        $this->assertEquals('Org\Snje\MinifwTest\Table\TableWithAll', get_class($table1));

        $table2 = TableWithOne::get();

        $this->assertEquals('Org\Snje\MinifwTest\Table\TableWithOne', get_class($table2));

        $this->assertNotEquals($table2, $table1);

        $table3 = TableWithAll::get();

        $this->assertEquals($table1, $table3);

        $table4 = TableWithAll::get([], 'def');

        $this->assertEquals($table1, $table4);
    }

}
