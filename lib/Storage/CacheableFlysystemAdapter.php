<?php
/**
 * @author Hemant Mann <hemant.mann121@gmail.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH.
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
use League\Flysystem\FileNotFoundException;
use OC\Files\Storage\Flysystem;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Plugin\GetWithMetadata;

/**
 * Generic Cacheable adapter between flysystem adapters and owncloud's storage system
 *
 * To use: subclass and call $this->buildFlysystem with the flysystem adapter of choice
 */
abstract class CacheableFlysystemAdapter extends Flysystem {
	/**
	 * This property is used to check whether the storage is case insensitive or not
	 * @var boolean
	 */
	protected $isCaseInsensitiveStorage = false;

	/**
	 * Stores the results in cache for the current request to prevent multiple requests to the API
	 * @var array
	 */
	protected $cacheContents = [];

	/**
	 * Initialize the storage backend with a flyssytem adapter
	 * Override parent method so the flysystem include information about storage case sensitivity
	 *
	 * @param \League\Flysystem\AdapterInterface $adapter
	 */
	protected function buildFlySystem(AdapterInterface $adapter) {
		$this->flysystem = new Filesystem($adapter, [Filesystem::IS_CASE_INSENSITIVE_STORAGE => $this->isCaseInsensitiveStorage]);
		$this->flysystem->addPlugin(new GetWithMetadata());
	}

	public function clearCache() {
		$this->cacheContents = [];
		return $this;
	}

	/**
	 * Get the location which will be used as a key in cache
	 * If Storage is not case sensitive then convert the key to lowercase
	 * @param  string $path Path to file/folder
	 * @return string
	 */
	public function getCacheLocation($path) {
		$location = $this->buildPath($path);
		if ($location === '') {
			$location = '/';
		}
		if ($this->isCaseInsensitiveStorage) {
			$location = \strtolower($location);
		}
		return $location;
	}

	/**
	 * Check if Cache Contains the data for given path, if not then get the data
	 * from flysystem and store it in cache for this request lifetime
	 * @param  string $path Path to file/folder
	 * @return array|boolean
	 */
	public function getFlysystemMetadata($path, $overRideCache = false) {
		$location = $this->getCacheLocation($path);
		if (!isset($this->cacheContents[$location]) || $overRideCache) {
			try {
				$this->cacheContents[$location] = $this->flysystem->getMetadata($location);
			} catch (FileNotFoundException $e) {
				// do not store this info in cache as it might interfere with Upload process
				return false;
			}
		}
		return $this->cacheContents[$location];
	}

	/**
	 * Store the list of files/folders in the cache so that subsequent requests in the
	 * same request life cycle does not call the flysystem API
	 * @param  array $contents Return value of $this->flysystem->listContents
	 */
	public function updateCache($contents) {
		foreach ($contents as $object) {
			$path = $object['path'];
			$this->cacheContents[$path] = $object;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function opendir($path) {
		try {
			$location = $this->buildPath($path);
			$content = $this->flysystem->listContents($location);
			$this->updateCache($content);
		} catch (FileNotFoundException $e) {
			return false;
		}
		$names = \array_map(function ($object) {
			return $object['basename'];
		}, $content);
		return IteratorDirectory::wrap($names);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fopen($path, $mode) {
		switch ($mode) {
			case 'r':
			case 'rb':
				return parent::fopen($path, $mode);

			default:
				unset($this->cacheContents[$this->getCacheLocation($path)]);
				return parent::fopen($path, $mode);
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filesize($path) {
		if ($this->is_dir($path)) {
			return 0;
		} else {
			$info = $this->getFlysystemMetadata($path);
			return (int) $info['size'];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function filemtime($path) {
		$info = $this->getFlysystemMetadata($path);
		if ($info) {	// if $path exists
			return $info['timestamp'];
		}
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function stat($path) {
		$info = $this->getFlysystemMetadata($path);
		if ($info) {
			return [
				'mtime' => $info['timestamp'],
				'size' => $info['size']
			];
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filetype($path) {
		if ($path === '' or $path === '/' or $path === '.') {
			return 'dir';
		}
		$info = $this->getFlysystemMetadata($path);
		if ($info) {
			return $info['type'];
		}
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function file_exists($path) {
		$info = $this->getFlysystemMetadata($path);
		return (bool) $info;
	}

	/**
	 * Set the cacheContents for the given path to false instead of null
	 * to prevent request to external storage
	 *
	 * Should be used when we know that the querying this path in the
	 * adapter will return false (i.e path not exists in external storage)
	 * @param  string $path Path to file/folder
	 */
	protected function removeStatCache($path) {
		$location = $this->getCacheLocation($path);
		$this->cacheContents[$location] = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rmdir($path) {
		$success = parent::rmdir($path);
		if ($success) {
			$this->removeStatCache($path);
		}
		return $success;
	}

	/**
	 * {@inheritdoc}
	 */
	public function unlink($path) {
		$success = parent::unlink($path);
		if ($success) {
			$this->removeStatCache($path);
		}
		return $success;
	}
}
