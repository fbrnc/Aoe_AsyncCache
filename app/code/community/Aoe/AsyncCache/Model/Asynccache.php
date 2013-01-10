<?php

/**
 * Async class
 * 
 * @author Fabrizio Branca
 *
 * @method getId()
 * @method setId()
 * @method getTstamp()
 * @method setTstamp()
 * @method getMode()
 * @method setMode()
 * @method getTags()
 *
 * @method setTags()
 * @method getTrace()
 * @method setTrace()
 * @method getStatus()
 * @method setStatus()
 * @method getProcessed()
 * @method setProcessed()
 */
class Aoe_AsyncCache_Model_Asynccache extends Mage_Core_Model_Abstract {

	const STATUS_PENDING = 'pending';

	/**
	 * Constructor
	 */
	protected function _construct() {
		$this->_init('aoeasynccache/asynccache');
	}
	
}
