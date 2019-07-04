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

namespace OCA\Files_external_onedrive\AppInfo;

use OCP\AppFramework\App;
use OCA\Files_External\Lib\Config\IBackendProvider;
use OCA\Files_External\Service\BackendService;

/**
 * @package OCA\Files_external_onedrive\AppInfo
 */
class Application extends App implements IBackendProvider {

    public function __construct(array $urlParams = []) {
        parent::__construct('files_external_onedrive', $urlParams);
    }

    /**
     * @{inheritdoc}
     */
    public function getBackends() {
        $container = $this->getContainer();
        $backends = [
            $container->query('OCA\Files_external_onedrive\Backend\OneDrive'),
        ];
        return $backends;
    }

    public function register()
    {
        $container = $this->getContainer();
        $server = $container->getServer();
        $backendService = $server->query('OCA\\Files_External\\Service\\BackendService');
        $backendService->registerBackendProvider($this);
    }

}
