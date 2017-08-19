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

namespace Org\Snje\MinifwTest\Text;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

/**
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class TextTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Text::strip_html
     */
    public function test_strip_html() {
        $hash = array(
            " 123456   \n  2345 \n" => "123456 2345",
            " 123456\n2345 \n" => "123456 2345",
            " 123456\n   //not show  \n2345 \n" => "123456 2345",
            "> 12345 <" => ">12345<",
            ">\n12345 <" => ">12345<",
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Text::strip_html($k));
        }
    }

    /**
     * @covers Org\Snje\Minifw\Text::strip_tags
     */
    public function test_strip_tag() {
        $hash = array(
            "<p>123</p>" => "123",
            "<br />123<br/>" => "123",
            "<p style=\"font-size:12px; color:red\">123</p>" => "123",
            "<p style=\"font-size:12px; color:red\" >123</p>" => "123",
            "<?ss style=\"font-size:12px; color:red\" >123</p>" => "<?ss style=\"font-size:12px; color:red\" >123",
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Text::strip_tags($k));
        }
    }

}
