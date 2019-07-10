<?php
namespace OCA\Files_external_onedrive\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ExternalConfig extends Entity implements JsonSerializable {

    protected $config_id;
    protected $mount_id;
    protected $key;
    protected $value;

    public function jsonSerialize() {
        return [
            'configId' => $this->config_id,
            'mountId' => $this->mount_id,
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}