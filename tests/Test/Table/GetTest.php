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

namespace Org\Snje\MinifwTest\Test\Table;

use Org\Snje\MinifwTest\Data\Table;

/**
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class GetTest extends \PHPUnit_Framework_TestCase {

    public function test_get() {
        $table1 = Table\Table1::get();

        $this->assertEquals('Org\\Snje\\MinifwTest\\Data\\Table\\Table1', get_class($table1));

        $table2 = Table\Table2::get();

        $this->assertEquals('Org\\Snje\\MinifwTest\\Data\\Table\\Table2', get_class($table2));

        $this->assertNotEquals($table2, $table1);

        $table3 = Table\Table1::get();

        $this->assertEquals($table1, $table3);
    }

}
