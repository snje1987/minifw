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

namespace Org\Snje\MinifwTest\Router;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

/**
 * Description of Get
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class RouterTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Text::strip_html
     */
    public function test_multi_layer_info() {
        $hash = [
            '/' => ['', '', [], ''],
            '/index' => ['', 'index', [], ''],
            '/www/index' => ['/www', 'index', [], ''],
            '/www/index-' => ['/www', 'index', [''], ''],
            '/www/qqq/index-12' => ['/www/qqq', 'index', ['12'], '12'],
            '/www/qqq/index-1-2-3' => ['/www/qqq', 'index', ['1', '2', '3'], '1-2-3'],
            '//www/qqq/index-1-2' => ['//www/qqq', 'index', ['1', '2'], '1-2'],
        ];
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Router::multi_layer_info($k));
        }
        $err = ['', 'www/3eee', '/www\\/qqq/index-1-2'];
        foreach ($err as $v) {
            try {
                FW\Router::multi_layer_info($v);
                $this->assertTrue(false);
            } catch (\Org\Snje\Minifw\Exception $ex) {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @covers Org\Snje\Minifw\Text::strip_tags
     */
    public function test_single_layer_info() {
        $hash = [
            '/' => ['', '', ''],
            '/index' => ['', 'index', ''],
            '/www/index' => ['/www', 'index', ''],
            '/www/index-' => ['/www', 'index', '-'],
            '/www/qqq/index-12' => ['/www', 'qqq', '/index-12'],
            '//www/qqq/index-1-2' => ['/', 'www', '/qqq/index-1-2'],
            '/www\\/qqq/index-1-2' => ['', 'www', '\\/qqq/index-1-2'],
        ];
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Router::single_layer_info($k));
        }
        $err = ['', 'www/3eee'];
        foreach ($err as $v) {
            try {
                FW\Router::single_layer_info($v);
                $this->assertTrue(false);
            } catch (\Org\Snje\Minifw\Exception $ex) {
                $this->assertTrue(true);
            }
        }
    }

}
