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

use Org\Snje\Minifw\Text;

/**
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class TestTest extends \PHPUnit_Framework_TestCase {

    public function test_strip_html() {
        $hash = [
            " 123456   \n  2345 \n" => "123456 2345",
            " 123456\n2345 \n" => "123456 2345",
            " 123456\n   //not show  \n2345 \n" => "123456 2345",
            "> 12345 <" => ">12345<",
            ">\n12345 <" => ">12345<",
        ];
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, Text::strip_html($k));
        }
    }

    public function test_strip_tag() {
        $hash = [
            "<p>123</p>" => "123",
            "<br />123<br/>" => "123",
            "<p style=\"font-size:12px; color:red\">123</p>" => "123",
            "<p style=\"font-size:12px; color:red\" >123</p>" => "123",
            "<?ss style=\"font-size:12px; color:red\" >123</p>" => "<?ss style=\"font-size:12px; color:red\" >123",
        ];
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, Text::strip_tags($k));
        }
    }

}
