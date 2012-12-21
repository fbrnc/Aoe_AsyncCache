<?php

class Aoe_AsyncCache_Model_JobCollection extends Varien_Data_Collection {

	/**
	 * Check for duplicates before adding new job to the collection
	 *
	 * @param Aoe_AsyncCache_Model_Job $job
	 * @return Aoe_AsyncCache_Model_JobCollection
	 */
	public function addItem(Aoe_AsyncCache_Model_Job $job) {
		// check if job with same mode and tags already exists
		foreach ($this->getItems() as $existingJob) { /* @var $existingJob Aoe_AsyncCache_Model_Job */
			if ($existingJob->isEqualTo($job)) {
				return $this;
			}
		}
		return parent::addItem($job);
	}

	/**
	 * Summary for Aoe_Scheduler output
	 *
	 * @return array
	 */
	public function getSummary() {
		return array();
	}

}