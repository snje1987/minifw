<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class DiffTest extends Ts\TestCommon {

    public function test_add() {
        $table = TableAdd::get();
        $diff = $table->table_diff();
        $this->assertEquals(TableAdd::DIFF, $diff);
    }

    public function test_del() {
        $table = TableDel::get();
        $diff = $table->table_diff();
        $this->assertEquals(TableDel::DIFF, $diff);
    }

    public function test_change() {
        $table = TableChange::get();
        $diff = $table->table_diff();
        $this->assertEquals(TableChange::DIFF, $diff);
    }

    public function test_move() {
        $table = TableMove::get();
        $diff = $table->table_diff();
        $this->assertEquals(TableMove::DIFF, $diff);
    }

    public function test_get_all_diff() {
        $diff = FW\TableUtils::get_all_diff('Org\\Snje\\MinifwTest\\Table', __DIR__);
        foreach ($diff as $class => $info) {
            $this->assertEquals($class::TBNAME, $info['tbname']);
            $this->assertEquals($class::DIFF, $info['diff']);
        }
    }

    public function test_display_all_diff() {
        ob_start();
        FW\TableUtils::display_all_diff('Org\\Snje\\MinifwTest\\Table', __DIR__);
        $content = ob_get_clean();
        $str = file_get_contents(__DIR__ . '/diff_output');
        $this->assertEquals($str, $content);
    }

}
