<?php

/**
 * Asynccache
 * 
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Mysql4_Asynccache extends Mage_Core_Model_Mysql4_Abstract {
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	protected function _construct()	{
		$this->_init('aoeasynccache/asynccache', 'id');
	}
}
