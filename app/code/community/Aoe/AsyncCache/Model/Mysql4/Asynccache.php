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

    /**
     * Overwritten save method, updates data on duplicate key
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Aoe_AsyncCache_Model_Mysql4_Asynccache
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_serializeFields($object);
        $this->_beforeSave($object);

        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getMainTable(),
            $this->_prepareDataForSave($object),
            $this->_fieldsForUpdate
        );

        $this->unserializeFields($object);
        $this->_afterSave($object);

        return $this;
    }

}
