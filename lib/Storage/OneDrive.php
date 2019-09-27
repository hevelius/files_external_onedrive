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

use Icewind\Streams\IteratorDirectory;
use Icewind\Streams\RetryWrapper;
use OCP\Files\Storage\FlysystemStorageAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use Microsoft\Graph\Graph;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;

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

	private $id;
	private $options;
	protected $adapter;
	protected $logger;
	protected $flysystem;
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
		$app = new \OCP\AppFramework\App(self::APP_NAME);
		$container = $app->getContainer();
		$this->server = $container->getServer();
		$user = $this->server->getUserSession()->getUser();

		if ($user == null) {
			throw new \Exception('OneDrive user storage not defined');
		} else {

			if (isset($params['client_id']) && isset($params['client_secret']) && isset($params['token']) && isset($params['configured']) && $params['configured'] === 'true') {
				$this->clientId = $params['client_id'];
				$this->clientSecret = $params['client_secret'];
				
				

				$this->token = json_decode(gzinflate(base64_decode($params['token'])));

				if ($this->token !== null) {
					$now = time() + 300;
					if ($this->token->expires <= $now) {
						$this->token = json_decode(gzinflate(base64_decode($this->refreshToken($this->token))));
					}
				}

				$this->accessToken = $this->token->access_token;

				$this->client = new Graph();
				$this->client->setAccessToken($this->accessToken);

				$this->root = isset($params['root']) ? $params['root'] : '/';

				$this->id = 'onedrive::' . $this->clientId . '::' . $this->token->code_uid;

				$adapter = new Adapter($this->client, 'root', '/me/drive/', true);

				$cacheStore = new MemoryStore();
				$this->adapter = new CachedAdapter($adapter, $cacheStore);

				$this->buildFlySystem($this->adapter);
				$this->logger = \OC::$server->getLogger();
			} else if (isset($params['configured']) && $params['configured'] === 'false') {
				throw new \Exception('OneDrive storage not yet configured');
			} else {
				throw new \Exception('Creating OneDrive storage failed');
			}
			
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function test()
	{
		// TODO: add test Storage
		return true;
	}

	public function filemtime($path)
	{
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
				$arr[] = isset($c['timestamp']) ? $c['timestamp'] : 0;
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

	public function file_exists($path)
	{
		if ($path === '' || $path === '/' || $path === '.') {
			return true;
		}
		return parent::file_exists($path);
	}

	protected function getLargest($arr, $default = 0)
	{
		if (count($arr) === 0) {
			return $default;
		}
		arsort($arr);
		return array_values($arr)[0];
	}

	public function refreshToken()
	{
		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'          => $this->clientId,
			'clientSecret'      => $this->clientSecret,
			'redirectUri'       => '',
			'urlAuthorize'            => "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
			'urlAccessToken'          => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
			'urlResourceOwnerDetails' => '',
			'scopes'					  => 'Files.Read Files.Read.All Files.ReadWrite Files.ReadWrite.All User.Read Sites.ReadWrite.All offline_access'
		]);

		$newToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $this->token->refresh_token
		]);

		$newToken = json_encode($newToken);
		$newToken = json_decode($newToken, true);
		$newToken['code_uid'] = $this->token->code_uid;

		$newToken = base64_encode(gzdeflate(json_encode($newToken), 9));

		$DBConfigService = $this->server->query('OCA\\Files_External\\Service\\DBConfigService');

		$user = $this->server->getUserSession()->getUser();

		if ($user == null) {
			throw new \Exception('OneDrive storage user could not be null');
		}

		$mountId = null;
		$mounts = $DBConfigService->getUserMountsFor(3, $user->getUID());

		foreach ($mounts as $mount) {
			if ($mount['config']['client_id'] == $this->clientId) {
				$mountId = $mount['mount_id'];
				break;
			}
		}

		if ($mountId == null) {
			$mounts = $DBConfigService->getAdminMountsFor(3, $user->getUID());

			foreach ($mounts as $mount) {
				if ($mount['config']['client_id'] == $this->clientId) {
					$mountId = $mount['mount_id'];
					break;
				}
			}
		}

		if ($mountId == null) {
			throw new \Exception('OneDrive storage not yet configured');
		}

		$key = "token";

		$DBConfigService->setConfig($mountId, $key, $newToken);

		return $newToken;
	}
}
