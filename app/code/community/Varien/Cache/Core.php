<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Cache
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Varien_Cache_Core extends Zend_Cache_Core
{

	/**
	 * @var int default cache priority
	 */
    protected $defaultPriority = 8;

    /**
     * Set default priority
     *
     * @param int $defaultPriority
     * @return Varien_Cache_Core
     */
    public function setDefaultPriority($defaultPriority) {
        $this->defaultPriority = $defaultPriority;
        return $this;
    }

	/**
	 * Make and return a cache id
	 *
	 * Checks 'cache_id_prefix' and returns new id with prefix or simply the id if null
	 *
	 * @param  string $id Cache id
	 * @return string Cache id (with or without prefix)
	 */
	protected function _id($id)
	{
		if ($id !== null) {
			$id = preg_replace('/([^a-zA-Z0-9_]{1,1})/', '_', $id);
			if (isset($this->_options['cache_id_prefix'])) {
				$id = $this->_options['cache_id_prefix'] . $id;
			}
		}
		return $id;
	}

	/**
	 * Prepare tags
	 *
	 * @param array $tags
	 * @return array
	 */
	protected function _tags($tags)
	{
		foreach ($tags as $key=>$tag) {
			$tags[$key] = $this->_id($tag);
		}
		return $tags;
	}

	/**
	 * Save some data in a cache
	 *
	 * @param  mixed $data           Data to put in cache (can be another type than string if automatic_serialization is on)
	 * @param  string $id             Cache id (if not set, the last cache id will be used)
	 * @param  array $tags           Cache tags
	 * @param  int $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
	 * @param  int   $priority         integer between 0 (very low priority) and 10 (maximum priority) used by some particular backends
	 * @throws Zend_Cache_Exception
	 * @return boolean True if no problem
	 */
	public function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = NULL)
	{
        if (is_null($priority)) {
            $priority = $this->defaultPriority;
        }
		$tags = $this->_tags($tags);
		return parent::save($data, $id, $tags, $specificLifetime, $priority);
	}

	/**
	 * Clean cache entries
	 *
	 * Available modes are :
	 * 'all' (default)  => remove all cache entries ($tags is not used)
	 * 'old'            => remove too old cache entries ($tags is not used)
	 * 'matchingTag'    => remove cache entries matching all given tags
	 *                     ($tags can be an array of strings or a single string)
	 * 'notMatchingTag' => remove cache entries not matching one of the given tags
	 *                     ($tags can be an array of strings or a single string)
	 * 'matchingAnyTag' => remove cache entries matching any given tags
	 *                     ($tags can be an array of strings or a single string)
	 *
	 * @param  string       $mode
	 * @param  array|string $tags
	 * @param  bool			if true the cache will be really deleted, otherwise it will be written to a queue
	 * @throws Zend_Cache_Exception
	 * @return boolean True if ok
	 */
	public function clean($mode = 'all', $tags = array(), $doIt = false) {

		if (Mage::registry('ignoreCacheCleaning')) {
			return true;
		}

		if (!$doIt && !Mage::registry('disableasynccache')) {
			$asynccache = Mage::getModel('aoeasynccache/asynccache'); /* @var $asynccache Aoe_AsyncCache_Model_Asynccache */
			if ($asynccache !== false) {
				// Mage::log('Logging clear cache request');
				$asynccache->setTstamp(time());
				$asynccache->setMode($mode);
				$asynccache->setTags(is_array($tags) ? implode(',', $tags) : $tags);
				$asynccache->setStatus(Aoe_AsyncCache_Model_Asynccache::STATUS_PENDING);
				/*
				if (Mage::getStoreConfig('system/aoeasynccache/enabletrace') == 1) {
					$asynccache->setTrace(Mage::app()->getHelper('aoeasynccache')->debugTrail());
				}
				*/
				try {
					$asynccache->save();
					return true;
				} catch (Zend_Db_Statement_Exception $e) {
					if ($e->getCode() == 23000) { // Integrity constraint violation: 1062 Duplicate entry '...' for key 'IDX_ASYNCCACHE_MODE_TAGS_STATUS'
						// this record already exists => everything's ok :)
						return true;
					}
				} catch (Exception $e) {
					// Table might not be created yet. Just go on without returning...
				}
			}
		}

		if (in_array('MAGE', $tags)) {
			// Cleaning all cache is way faster than cleaning by tag.
			// And some backends (like apc) only don't support cleaning by tag.
			// "MAGE" matches all cache entries in most cases anyways.
			$mode = 'all';
		}

		Mage::dispatchEvent('clean_cache', array('mode' => $mode, 'tags' => $tags));

		$tags = $this->_tags($tags);
		$res = parent::clean($mode, $tags);
		if (!$res) {
			Mage::log('Cleaning the cache (mode: '.$mode.') did not return true', Zend_Log::ERR);
		}
		return $res;
	}

	/**
	 * Return an array of stored cache ids which match given tags
	 *
	 * In case of multiple tags, a logical AND is made between tags
	 *
	 * @param array $tags array of tags
	 * @return array array of matching cache ids (string)
	 */
	public function getIdsMatchingTags($tags = array())
	{
		$tags = $this->_tags($tags);
		return parent::getIdsMatchingTags($tags);
	}

	/**
	 * Return an array of stored cache ids which don't match given tags
	 *
	 * In case of multiple tags, a logical OR is made between tags
	 *
	 * @param array $tags array of tags
	 * @return array array of not matching cache ids (string)
	 */
	public function getIdsNotMatchingTags($tags = array())
	{
		$tags = $this->_tags($tags);
		return parent::getIdsNotMatchingTags($tags);
	}
}