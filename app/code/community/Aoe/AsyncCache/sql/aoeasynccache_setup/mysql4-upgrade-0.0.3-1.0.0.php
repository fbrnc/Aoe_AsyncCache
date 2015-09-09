<?php

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();
$table = $installer->getTable('aoeasynccache/asynccache');
$connection = $installer->getConnection();

// truncate table first
$connection->truncate($table);

// add index
$query = 'CREATE UNIQUE INDEX IDX_ASYNCCACHE_MODE_TAGS_STATUS ON '. $table . ' (`mode`, `tags`, `status`)';
$connection->raw_query($query);

$installer->endSetup();