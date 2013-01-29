<?php

/**
 * Cleaner
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Cleaner extends Mage_Core_Model_Abstract {

	/**
	 * Process the queue
	 *
	 * @return array|null
	 */
	public function processQueue() {
		$jobs = null;
		$collection = $this->getUnprocessedEntriesCollection();
		$configCacheWasCleaned = false;
		if (count($collection) > 0) {
			$jobs = $collection->extractJobs();
			foreach ($jobs as &$job) {
				if (in_array(Mage_Core_Model_Config::CACHE_TAG, $job['tags']) || $job['mode'] == Zend_Cache::CLEANING_MODE_ALL) {
					$configCacheWasCleaned = true;
				}
				$startTime = time();
				Mage::app()->getCache()->clean($job['mode'], $job['tags'], true);
				$job['duration'] = time() - $startTime;
                if (Mage::getStoreConfigFlag('dev/log/aoeAsyncCacheActive')) {
				    Mage::log('[ASYNCCACHE] MODE: ' . $job['mode'] . ', DURATION: ' . $job['duration'] . ' sec, TAGS: ' . implode(', ', $job['tags']));
                }
			}

			// delete all affected asynccache database rows
			foreach ($collection as $asynccache) { /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
				$asynccache->delete();
			}

		}

		// disabling asynccache (clear cache requests will be processed right away) for all following requests in this script call
		Mage::register('disableasynccache', true, true);

		// Following code leads to problems while reinit config. Commenting it out for now
		/*
		if ($configCacheWasCleaned) {
			try {
				// reinit configuration will trigger a clear config cache
				Mage::app()->getConfig()->reinit();
			} catch(Exception $e) {
				Mage::log('[ASYNCCACHE] Error while config reinit: ' . $e->getMessage());
			}
		}
		*/

		return $jobs;
	}

	
	
	/**
	 * Get all unprocessed entries
	 *
	 * @return Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection
	 */
	public function getUnprocessedEntriesCollection() {
		$collection = Mage::getModel('aoeasynccache/asynccache')->getCollection(); /* @var $collection Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection */
		$collection->addFieldToFilter('tstamp', array('lteq' => time()));
		$collection->addFieldToFilter('status', 'pending');
		return $collection;
	}

}
