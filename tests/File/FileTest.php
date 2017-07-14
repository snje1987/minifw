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
class FileTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Text::strip_html
     */
    public function test_copy() {
        $base = str_replace('\\', '/', dirname(__FILE__));
        $from = $base . '/from/file1';
        $to = $base . '/to/file1';
        FW\File::copy($from, $to);
        $this->assertFileEquals($from, $to);
    }

    /**
     * @covers Org\Snje\Minifw\Text::strip_tags
     */
    public function test_delete() {
        $base = str_replace('\\', '/', dirname(__FILE__));
        $path = $base . '/to/file1';
        FW\File::delete($path, true);
        $this->assertFileNotExists($path);
    }

    public function test_copy_dir() {
        $base = str_replace('\\', '/', dirname(__FILE__));
        $from = $base . '/from/dir';
        $to = $base . '/to/dir';
        FW\File::copy_dir($from, $to);
        $this->assertFileEquals($from . '/dirfile1', $to . '/dirfile1');
        $this->assertFileEquals($from . '/sub/dirfile2', $to . '/sub/dirfile2');
    }

    public function test_clear_dir() {
        $base = str_replace('\\', '/', dirname(__FILE__));
        $path = $base . '/to/dir';
        FW\File::clear_dir($path, true);
        $this->assertFileNotExists($path . '/sub');
        $this->assertFileNotExists($path . '/dirfile1');
        $this->assertFileExists($path);
        FW\File::delete($path, true);
        $this->assertFileNotExists($path);
        $this->assertFileNotExists(dirname($path));
    }

}
