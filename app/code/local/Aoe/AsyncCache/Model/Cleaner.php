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
		if (count($collection) > 0) {
			$jobs = $collection->extractJobs();
			foreach ($jobs as &$job) {
				$startTime = time();
				Mage::app()->getCache()->clean($job['mode'], $job['tags'], true);
				$job['duration'] = time() - $startTime;
				Mage::log('[ASYNCCACHE] MODE: ' . $job['mode'] . ', DURATION: ' . $job['duration'] . ' sec, TAGS: ' . implode(', ', $job['tags']));
			}
			$processingTime = time();
			foreach ($collection as $asynccache) { /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
				$asynccache->setProcessed($processingTime);
				$asynccache->setStatus('success');
				$asynccache->save();
			}
		}

		// disabling asynccache (clear cache requests will be processed right away) for all following requests in this script call
		Mage::register('disableasynccache', true, true);

		try {
			// reinit configuration will trigger a clear config cache
			Mage::app()->getConfig()->reinit();
		} catch(Exception $e) {
			Mage::log('[ASYNCCACHE] Error while config reinit: ' . $e->getMessage());
		}

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
