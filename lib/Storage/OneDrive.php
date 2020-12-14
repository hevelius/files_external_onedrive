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

namespace OCA\Files_external_onedrive\Storage;

use Microsoft\Graph\Graph;
use OC\Files\Storage\Flysystem;

class OneDrive extends CacheableFlysystemAdapter
{

	const APP_NAME = 'files_external_onedrive';

	/**
	 * @var string
	 */
	protected $clientId;

	/**
	 * @var string
	 */
	protected $clientSecret;

	/**
	 * @var string
	 */
	protected $accessToken;

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var Adapter
	 */
	protected $adapter;

	/**
	 * @var Adapter
	 */
	protected $flysystem;

	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * @var int
	 */
	protected $cacheFilemtime = [];

	/**
	 * Initialize the storage backend with a flysytem adapter
	 * @override
	 * @param \League\Flysystem\Filesystem $fs
	 */
	public function setFlysystem($fs)
	{
		$this->flysystem = $fs;
		$this->flysystem->addPlugin(new \League\Flysystem\Plugin\GetWithMetadata());
	}
	public function setAdapter($adapter)
	{
		$this->adapter = $adapter;
	}

	public function __construct($params)
	{
		if (isset($params['client_id']) && isset($params['client_secret']) && isset($params['token']) && isset($params['configured']) && $params['configured'] === 'true') {
			$this->clientId = $params['client_id'];
			$this->clientSecret = $params['client_secret'];

			$this->root = isset($params['root']) ? $params['root'] : '/';

			$this->token = json_decode(gzinflate(base64_decode($params['token'])));

			$this->accessToken = $this->token->access_token;
			$this->id = 'onedrive::' . substr($this->clientId, 0, 8) . '::' . $this->token->code_uid;

			$this->client = new Graph();
			$this->client->setAccessToken($this->accessToken);

			$this->adapter = new Adapter($this->client, 'root', '/me/drive/', true);

			$this->buildFlySystem($this->adapter);
			$this->logger = \OC::$server->getLogger();
		} else if (isset($params['configured']) && $params['configured'] === 'false') {
			throw new \Exception('OneDrive storage not yet configured');
		} else {
			throw new \Exception('Creating OneDrive storage failed');
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function test()
	{
		return !$this->isTokenExpired();
	}

	public function file_exists($path) {
		if ($path === '' || $path === '/' || $path === '.') {
			return true;
		}
		return parent::file_exists($path);
	}

	protected function getLargest($arr, $default = 0) {
		if (\count($arr) === 0) {
			return $default;
		}
		\arsort($arr);
		return \array_values($arr)[0];
	}

	public function filemtime($path) {
		if ($this->is_dir($path)) {
			if ($path === '.' || $path === '') {
				$path = "/";
			}

			if ($this->cacheFilemtime && isset($this->cacheFilemtime[$path])) {
				return $this->cacheFilemtime[$path];
			}

			$arr = [];
			$contents = $this->flysystem->listContents($path, true);
			foreach ($contents as $c) {
				$arr[] = $c['type'] === 'file' ? $c['timestamp'] : 0;
			}
			$mtime = $this->getLargest($arr);
		} else {
			if ($this->cacheFilemtime && isset($this->cacheFilemtime[$path])) {
				return $this->cacheFilemtime[$path];
			}
			$mtime = parent::filemtime($path);
		}
		$this->cacheFilemtime[$path] = $mtime;
		return $mtime;
	}

	public function stat($path) {
		if ($path === '' || $path === '/' || $path === '.') {
			return ['mtime' => 0];
		}
		return parent::stat($path);
	}

	public function isTokenExpired()
	{
		if ($this->token !== null) {
			$now = time() + 900;
			if ($this->token->expires <= $now) {
				return true;
			}
		}

		return false;
	}

	public function refreshToken($clientId, $clientSecret, $token)
	{
		$token = json_decode(gzinflate(base64_decode($token)));
		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'          => $clientId,
			'clientSecret'      => $clientSecret,
			'redirectUri'       => '',
			'urlAuthorize'            => "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
			'urlAccessToken'          => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
			'urlResourceOwnerDetails' => '',
			'scopes'	=> 'Files.Read Files.Read.All Files.ReadWrite Files.ReadWrite.All User.Read Sites.ReadWrite.All offline_access'
		]);

		$newToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $token->refresh_token
		]);

		$newToken = json_encode($newToken);
		$newToken = json_decode($newToken, true);
		$newToken['code_uid'] = $token->code_uid;

		$newToken = base64_encode(gzdeflate(json_encode($newToken), 9));

		return $newToken;
	}
}
