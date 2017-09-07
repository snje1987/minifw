<?php

namespace Org\Snje\MinifwTest\DB;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class MysqliTest extends Ts\TestCommon {

    public function test_compile_sql() {
        $cases = [
            [
                'sql' => 'select {field1},{expr1} from {table} where {field2}={value1} or {field2}={rich_value} or {field1} like {like_value} or {field1} = {plain_value} or {expr}',
                'field' => [
                    'field1' => 'real_field1',
                    'field2' => ['', 'real_field2'],
                    'expr1' => ['expr', 'count(*)'],
                    'table' => 'test_table'
                ],
                'value' => [
                    'value1' => 'real_value1<>%_',
                    'rich_value' => ['rich', 'rich_alue<>%_'],
                    'like_value' => ['like', 'like_value<>%_'],
                    'plain_value' => ['', 'plain_value<>%_'],
                    'expr' => ['expr', '23=12+5'],
                ],
                'out' => 'select `real_field1`,count(*) from `test_table` where `real_field2`="real_value1&lt;&gt;%_" or `real_field2`="rich_alue<>%_" or `real_field1` like "like\_value<>\%\_" or `real_field1` = "plain_value&lt;&gt;%_" or 23=12+5',
            ]
        ];
        $db = FW\DB\Mysqli::get();
        foreach ($cases as $v) {
            $this->assertEquals($v['out'], $db->compile_sql($v['sql'], $v['field'], $v['value']));
        }
    }

}
