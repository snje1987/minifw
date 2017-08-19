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

/**
 * This is a sample index file, put it into the "www" folder and put ".htaccess"
 * file and "config.php" as the sample in the web root.
 */

namespace Site;

use Org\Snje\Minifw as FW;

require_once '../vendor/autoload.php';
$app = FW\System::get(dirname(__DIR__) . '/config.php');
$app->reg_call('/^(.*)$/', function($path) {
    $router = new FW\Router();
    $router->multi_layer_route($path, 'Site\\Controler', 'Default');
});
$app->run();
