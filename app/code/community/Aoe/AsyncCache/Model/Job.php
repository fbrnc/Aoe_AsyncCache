<?php

/**
 * @method getTags()
 * @method getMode()
 * @method setDuration()
 * @method getDuration()
 * @method getIsProcessed()
 * @method setIsProcessed()
 * @method setAsynccacheId()
 * @method getAsynccacheId()
 */
class Aoe_AsyncCache_Model_Job extends Varien_Object {

	/**
	 * Check if this job equals to another job
	 *
	 * @param Aoe_AsyncCache_Model_Job $job
	 * @return bool
	 */
	public function isEqualTo(Aoe_AsyncCache_Model_Job $job) {
		return ($this->getMode() == $job->getMode()) && ($this->getTags() == $job->getTags());
	}

	/**
	 * Set mode and tags
	 *
	 * @param $mode
	 * @param $tags
	 * @return Aoe_AsyncCache_Model_Job
	 */
	public function setParameters($mode, array $tags) {
		if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
			$tags = array(); // we don't need any tags for mode 'all'
		}

		$this->setData('mode', $mode);
		$this->setData('tags', $tags);

		return $this;
	}

	/**
	 * Check if this job is affecting the config cache
	 *
	 * @return bool
	 */
	public function affectsConfigCache() {
		return in_array(Mage_Core_Model_Config::CACHE_TAG, $this->getTags()) || $this->getMode() == Zend_Cache::CLEANING_MODE_ALL;
	}

}