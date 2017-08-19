<?php

/*
 * Copyright (C) 2013 Yang Ming <yangming0116@gmail.com>
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
 * Load config from config file
 */
class Config {

    /**
     * @var static the instance
     */
    protected static $_instance = null;

    /**
     *
     * @param array $args
     * @return Config
     */
    public static function get($args = array()) {
        if (self::$_instance === null) {
            self::$_instance = new static($args);
        }
        return self::$_instance;
    }

    public static function get_new($args = array()) {
        if (self::$_instance !== null) {
            self::$_instance = null;
        }
        return self::get($args);
    }

    /**
     * @var array config data
     */
    protected $data;
    protected $config_path;

    public function __construct($config_path) {
        $this->config_path = $config_path;
        $this->load_config();
    }

    /**
     * Get Config item
     *
     * @param string $section Config section
     * @param string $key Config key
     * @param mixed $default Default value when not exists
     * @return mixed Config value
     */
    public function get_config($section, $key = '', $default = null) {
        if ($section === '' || !isset($this->data[$section])) {
            return null;
        }
        if ($key === '') {
            return $this->data[$section];
        }
        if (!isset($this->data[$section][$key])) {
            return $default;
        }
        return $this->data[$section][$key];
    }

    /**
     * Load config data
     */
    public function load_config() {
        $cfg = array();
        require __DIR__ . '/defaults.php';
        if (file_exists($this->config_path)) {
            require $this->config_path;
        }
        $this->data = $cfg;
    }

}
