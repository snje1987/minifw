<?php

/*
 * Copyright (C) 2017 Yang Ming <yangming0116@163.com>
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

namespace Org\Snje\Minifw;

/**
 *
 * @author Yang Ming <yangming0116@163.com>
 */
interface TableAnalysis {

    /**
     * get info of the table(comment, engine ...)
     */
    public function get_table_status($tbname);

    /**
     * get indexs info of the table
     */
    public function get_table_index($tbname);

    /**
     * get fields info of the table
     */
    public function get_table_field($tbname);

    /**
     * get diff info to convert table status
     */
    public static function get_status_diff($tbname, $from, $to);

    /**
     * get diff info to convert table indexs
     */
    public static function get_index_diff($tbname, $from, $to);

    /**
     * get diff info to convert table fields
     */
    public static function get_field_diff($tbname, $from, $to);

    /**
     * get sql to create table
     */
    public static function create_table_sql($tbname, $tbinfo, $field, $index);

    public static function drop_table_sql($tbname);
}
