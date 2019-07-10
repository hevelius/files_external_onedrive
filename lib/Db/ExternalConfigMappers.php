<?php
namespace OCA\Files_external_onedrive\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class ExternalConfigMappers extends Mapper {

    public function __construct(IDbConnection $db) {
        parent::__construct($db, 'external_config', '\OCA\Files_external_onedrive\Db\ExternalConfig');
    }

    public function find($id) {
        $sql = 'SELECT * FROM *PREFIX*external_config WHERE config_id = ?';
        return $this->findEntity($sql, [$id]);
    }

    public function findByKey($mount_id, $key) {
        $sql = 'SELECT * FROM *PREFIX*external_config WHERE mount_id = ? AND `key` = ?';
        return $this->findEntity($sql, [$mount_id, $key]);
    }

    public function findAll($mount_id) {
        $sql = 'SELECT * FROM *PREFIX*external_config WHERE mount_id = ?';
        return $this->findEntities($sql, [$mount_id]);
    }

    public function updateTokenByMountIdAndKey($mount_id, $key, $token) {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName(), 'o')
        ->set("o.value", json_encode($token))
        ->where("o.mount_id = ? AND o.key = ?");
 		
 		$qb->execute();
    }

}