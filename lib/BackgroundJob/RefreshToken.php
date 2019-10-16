<?php
/**
 * @author Mario Perrotta <mario.perrotta@unimi.it>
 *
 * @copyright Copyright (c) 2019, Mario Perrotta, University of Milan
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

namespace OCA\Files_external_onedrive\BackgroundJob;

use OCA\Files_External\Lib\StorageConfig;
use OC\BackgroundJob\TimedJob;

class RefreshToken extends TimedJob {

	private $appName = 'files_external_onedrive';

	public function __construct() {
		$this->setInterval(60);
	}

	/**
	 * Get the storage configuration and refresh expired tokens only if the storage has configured = true
	 * Instantiate new OneDrive Storage class, Check is token is expired (expired within the next 15 minutes)
	 * if found then refresh the token and updates the external storage config
	 *
	 * @param  StorageConfig $storageConfig
	 * @return boolean      True on success, False on failure
	 */
	protected function refreshToken(StorageConfig $storageConfig) {
  		$opts = $storageConfig->getBackendOptions();
		if ($opts['configured'] === 'false') {
			return false;
		}
		try {
			$storage = new \OCA\Files_external_onedrive\Storage\OneDrive($opts);
			$key_token = "token";
			$token = $opts['token'];
			$clientId = $opts['client_id'];
			$clientSecret = $opts['client_secret'];
			if ($isTokenExpired = $storage->isTokenExpired()) {
				$token = $storage->refreshToken($clientId, $clientSecret, $token);
				$DBConfigService = \OC::$server->query('OCA\\Files_External\\Service\\DBConfigService');
				$DBConfigService->setConfig($storageConfig->getId(), $key_token, $token);	
			} else {
				$storage->getScanner()->scan('/', true);
			}
		} catch (\Exception $e) {
			return false;
		}
		return true;
	}

	protected function run($argument) {
		$service = \OC::$server->getGlobalStoragesService();
		$resp = $service->getStorageForAllUsers();
		$result = [];
		foreach ($resp as $r) {
			$data = $r->getBackend()->jsonSerialize();
			if ($data['identifier'] === $this->appName) {
				$result[] = $r;
			}
		}
		foreach ($result as $r) {
			$this->refreshToken($r);
		}
		return true;
	}
}
