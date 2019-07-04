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

/*$app = new \OCA\Files_external_onedrive\AppInfo\Application();

$app->registerRoutes($this, [
	'routes' => [
		['name' => 'oauth#receiveToken', 'url' => '/oauth', 'verb' => 'POST'],
	],
]);*/

if (\OC::$server->getAppManager()->isEnabledForUser('files_external')) {
    $application = new \OCA\Files_external_onedrive\AppInfo\Application();
    $application->registerRoutes($this, [
    	'routes' => [
    		['name' => 'oauth#receiveToken', 'url' => '/oauth', 'verb' => 'POST'],
    	],
    ]);
}
