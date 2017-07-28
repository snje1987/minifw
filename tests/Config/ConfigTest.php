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

namespace Org\Snje\MinifwTest\File;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

/**
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

    /**
     * @coversNothing
     */
    public static function setUpBeforeClass() {

    }

    public function test_config() {
        $cfg = [];
        require __DIR__ . '/../../src/defaults.php';
        require __DIR__ . '/config.php';
        $config_obj = FW\Config::get_new(__DIR__ . '/config.php');
        foreach ($cfg as $k => $v) {
            $this->assertEquals($v, $config_obj->get_config($k));
        }
        $this->assertNull($config_obj->get_config('sqlite', 'name'));
    }

}
