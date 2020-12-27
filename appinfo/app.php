<?php
/**
 * @author Mario Perrotta <mario.perrotta@unimi.it>
 *
 * @copyright Copyright (c) 2018, Mario Perrotta <mario.perrotta@unimi.it>
 * @license GPL-2.0
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

declare(strict_types=1);

if ((@include_once(dirname(__DIR__).'/vendor/autoload.php')) === false) {
    throw new \Exception('Cannot include autoload. Did you install dependencies using composer?');
}

$app = \OC::$server->query(Application::class);

if (!$app::isEnabled('files_external')) {
	$app::enable('files_external');
}

$app = new \OCA\Files_external_onedrive\AppInfo\Application();
$app->register();

