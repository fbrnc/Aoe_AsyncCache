<?php

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

// truncate table first
$installer->getConnection()->truncateTable($installer->getTable('aoeasynccache/asynccache'));

// add index
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', array('mode', 'tags', 'status')),
	array('mode', 'tags', 'status'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();