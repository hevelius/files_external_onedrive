<?php
namespace OCA\Files_external_onedrive\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ExternalConfig extends Entity implements JsonSerializable {

    protected $mount_id;
    protected $key;
    protected $value;

    public function jsonSerialize() {
        return [
            'config_id' => $this->id,
            'mount_id' => $this->mount_id,
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}