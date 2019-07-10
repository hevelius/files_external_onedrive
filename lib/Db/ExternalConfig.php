<?php
namespace OCA\Files_external_onedrive\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ExternalConfig extends Entity implements JsonSerializable {

    protected $configId;
    protected $mountId;
    protected $key;
    protected $value;

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'config_id' => $this->config_id,
            'mount_id' => $this->mount_id,
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}