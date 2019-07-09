<?php
// db/authordao.php

namespace OCA\Files_external_onedrive\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class StorageConfig {

    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function find(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from('external_config')
           ->where(
               $qb->expr()->eq('mount_id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
           );

        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        return $row;
    }

}