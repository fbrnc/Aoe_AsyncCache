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

        $summary = array();

		$collection = $this->getUnprocessedEntriesCollection(); /* @var $collection Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection */
		$configCacheWasCleaned = false;
		if (count($collection) > 0) {
			
			$jobCollection = $collection->extractJobs(); /* @var $jobCollection Aoe_AsyncCache_Model_JobCollection */

			// give other modules (e.g. Aoe_VarnishAsyncCache) to process jobs instead
			Mage::dispatchEvent('aoeasynccache_processqueue_preprocessjobcollection', array('jobCollection' => $jobCollection));

			foreach ($jobCollection as $job) { /* @var $job Aoe_AsyncCache_Model_Job */
				if ($job->affectsConfigCache()) {
					$configCacheWasCleaned = true;
				}

				if (!$job->getIsProcessed()) {
					$mode = $job->getMode();
					if (in_array($mode, array(Zend_Cache::CLEANING_MODE_ALL,
						 Zend_Cache::CLEANING_MODE_OLD,
						 Zend_Cache::CLEANING_MODE_MATCHING_TAG,
						 Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
						 Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG))) {

						$startTime = time();
						Mage::app()->getCache()->clean($job->getMode(), $job->getTags(), true);
						$job->setDuration(time() - $startTime);
						$job->setIsProcessed(true);

						Mage::log(sprintf('[ASYNCCACHE] MODE: %s, DURATION: %s sec, TAGS: %s',
							$job->getMode(),
							$job->getDuration(),
							implode(', ', $job->getTags())
						));
					}
				}
			}

			// give other modules (e.g. Aoe_VarnishAsyncCache) to process jobs instead
			Mage::dispatchEvent('aoeasynccache_processqueue_postprocessjobcollection', array('jobCollection' => $jobCollection));

			// check what jobs weren't processed by any code
			foreach ($jobCollection as $job) { /* @var $job Aoe_AsyncCache_Model_Job */
				if (!$job->getIsProcessed()) {
					Mage::log(sprintf('[ASYNCCACHE] Couldn\'t process job: MODE: %s, TAGS: %s',
						$job->getMode(),
						implode(', ', $job->getTags())
					), Zend_Log::ERR);
				}
			}

			// delete all affected asynccache database rows
			foreach ($collection as $asynccache) { /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
				$asynccache->delete();
			}

			$summary = $jobCollection->getSummary();
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

		return $summary;
	}

	
	
	/**
	 * Get all unprocessed entries
	 *
	 * @return Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection
	 */
	public function getUnprocessedEntriesCollection() {
		$collection = Mage::getModel('aoeasynccache/asynccache')->getCollection(); /* @var $collection Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection */
		$collection->addFieldToFilter('tstamp', array('lteq' => time()));
		$collection->addFieldToFilter('status', Aoe_AsyncCache_Model_Asynccache::STATUS_PENDING);

        // if configured, set limit to query
        $selectLimit = (int) Mage::getStoreConfig('system/aoeasynccache/select_limit');
        if ($selectLimit && ($selectLimit != 0)) {
            $collection->getSelect()->limit($selectLimit);
        }

		return $collection;
	}

}
