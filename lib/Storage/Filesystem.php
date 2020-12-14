<?php

/**
 * @author Mario Perrotta <mario.perrotta@unimi.it>
 *
 * @copyright Copyright (c) 2018, Mario Perrotta <mario.perrotta@unimi.it>
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_external_onedrive\Storage;

use League\Flysystem\Util;
use League\Flysystem\Filesystem as VendorFilesystem;

class Filesystem extends VendorFilesystem {
	const IS_CASE_INSENSITIVE_STORAGE = 'isCaseInsensitiveStorage';

	/**
	 * @inheritdoc
	 */
	public function listContents($directory = '', $recursive = false) {
		$directory = Util::normalizePath($directory);
		$contents = $this->getAdapter()->listContents($directory, $recursive);

		$contentListFormatter = new ContentListingFormatter($directory, $recursive);
		// Make the formatter aware of the storage type i.e. whether it is case insensitive or not
		$contentListFormatter->setIsCaseInsensitiveStorage($this->getConfig()->get(static::IS_CASE_INSENSITIVE_STORAGE, false));

		return $contentListFormatter->formatListing($contents);
	}
}
