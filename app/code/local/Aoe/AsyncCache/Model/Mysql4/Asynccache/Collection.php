<?php

/**
 * Async collection
 * 
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Mysql4_Asynccache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('aoeasynccache/asynccache');
    }
    
    /**
     * Extract jobs
	 * Combines job to reduce cache operations
     * 
     * @return array
     */
    public function extractJobs() {
    	$jobs = array();
    	$matchingAnyTag = array();
		foreach ($this as $asynccache) {
			$mode = $asynccache->getMode();
			$tags = $this->getTagArray($asynccache->getTags());
			if ($mode == 'all') {
				return array(array('mode' => 'all', 'tags' => array()));
			} elseif ($mode == 'matchingAnyTag') {
				$matchingAnyTag = array_merge($matchingAnyTag, $tags);
			} elseif (($mode == 'matchingTag') && (count($tags) <= 1)) {
				$matchingAnyTag = array_merge($matchingAnyTag, $tags);
			} else {
				$jobs = $this->addCustomJob($jobs, $mode, $tags);
			}
		}
		$matchingAnyTag = array_unique($matchingAnyTag);
		if (count($matchingAnyTag) > 0) {
			$jobs[] = array('mode' => 'matchingAnyTag', 'tags' => $matchingAnyTag);
		}
		return $jobs;
    }
    
    /**
     * Add job is it doesn't exist
     * 
     * @param array $jobs
     * @param string $mode
     * @param array $tags
     * @return array $jobs
     */
    protected function addCustomJob(array $jobs, $mode, $tags) {
    	foreach ($jobs as $job) {
    		if ($job['mode'] == $mode && $job['tags'] == $tags) {
    			return $jobs;
    		}
    	}
    	$jobs[] = array('mode' => $mode, 'tags' => $tags);
    	return $jobs;
    }
    
    /**
     * Get tag array from string
     * 
     * @param string $tagString
     * @return array
     */
    protected function getTagArray($tagString) {
    	$tags = array();
    	foreach (explode(',', $tagString) as $tag) {
			$tag = trim($tag);
			if (!empty($tag) && !in_array($tag, $tags)) {
				$tags[] = $tag;
			}
		}
		sort($tags);
		return $tags;
    }
    
}
