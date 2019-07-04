<?php
/**
 * @author Samy NASTUZZI <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, Samy NASTUZZI (samy@nastuzzi.fr).
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

use League\Flysystem\Config;
use League\Flysystem\Util;

class Adapter extends \NicolasBeauvais\FlysystemOneDrive\OneDriveAdapter
{

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    /*public function getMetadata($path)
    {
        $response = $this->client->getMetadata($path);
        $responseContent = json_decode((string) $response->getBody());
        $flysystemMetadata = new FlysystemMetadata(FlysystemMetadata::TYPE_FILE, $path);
        $this->updateFlysystemMetadataFromResponseContent($flysystemMetadata, $responseContent);
        return $flysystemMetadata->toArray();
    }*/
}
