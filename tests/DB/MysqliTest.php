<?php

namespace Org\Snje\MinifwTest\DB;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class MysqliTest extends Ts\TestCommon {

    public function test_compile_sql() {
        $cases = array(
            array(
                'sql' => 'select {name} from {table} limit 1',
                'field' => array(
                    'name' => 'real_name',
                    'table' => 'real_table'
                ),
                'value' => array(),
                'out' => 'select `real_name` from `real_table` limit 1',
            ),
            array(
                'sql' => 'select `name` from `table` where `name` = {value}',
                'field' => array(),
                'value' => array(
                    'value' => 'real_value<>_%'
                ),
                'out' => 'select `name` from `table` where `name` = "real_value&lt;&gt;_%"',
            ),
            array(
                'sql' => 'select `name` from `table` where `name` = {value}',
                'field' => array(),
                'value' => array(
                    'value' => array('rich', 'real_value<>_%'),
                ),
                'out' => 'select `name` from `table` where `name` = "real_value<>_%"',
            ),
            array(
                'sql' => 'select `name` from `table` where `name` = {value}',
                'field' => array(),
                'value' => array(
                    'value' => array('expr', '"real_value<>_%"'),
                ),
                'out' => 'select `name` from `table` where `name` = "real_value<>_%"',
            ),
            array(
                'sql' => 'select `name` from `table` where `name` like "%{value}_"',
                'field' => array(),
                'value' => array(
                    'value' => array('like', 'real_value<>_%'),
                ),
                'out' => 'select `name` from `table` where `name` like "%real\_value<>\_\%_"',
            ),
        );
        $db = FW\DB\Mysqli::get();
        foreach ($cases as $v) {
            $this->assertEquals($v['out'], $db->compile_sql($v['sql'], $v['field'], $v['value']));
        }
    }

}
