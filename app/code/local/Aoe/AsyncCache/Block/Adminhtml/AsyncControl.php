<?php

class Aoe_AsyncCache_Block_Adminhtml_AsyncControl extends Mage_Adminhtml_Block_Template {

	/**
	 * Get collection of async objects
	 *
	 * @return Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection
	 */
	public function getAsyncCollection() {
		$cleaner = Mage::getModel('aoeasynccache/cleaner'); /* @var $cleaner Aoe_AsyncCache_Model_Cleaner */
		return $cleaner->getUnprocessedEntriesCollection();
	}

}