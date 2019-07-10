<?php
namespace OCA\Files_external_onedrive\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ExternalConfig extends Entity implements JsonSerializable {

    protected $mountId;
    protected $key;
    protected $value;

    public function jsonSerialize() {
        return [
            'config_id' => $this->configId,
            'id' => $this->configId,
            'mount_id' => $this->mountId,
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}