<?php

class Aoe_AsyncCache_ApcController extends Mage_Core_Controller_Front_Action {

	/**
	 * Get Apc used size action
	 *
	 * @return void
	 */
	public function getApcUsedSizeAction() {
		$this->checkKey();
		$mem = apc_sma_info();
		$mem_size = $mem['num_seg']*$mem['seg_size'];
		$mem_avail= $mem['avail_mem'];
		$mem_used = round(($mem_size - $mem_avail)/1024);
		echo $mem_used;
	}

	/**
	 * Clear user cache
	 *
	 * @return void
	 */
	public function clearUserCacheAction() {
		$this->checkKey();
		if (apc_clear_cache('user')) {
			echo 'SUCCESS';
		} else {
			echo 'ERROR';
		}
	}

	/**
	 * Clear opcode cache
	 *
	 * @return void
	 */
	public function clearOpcodeCacheAction() {
		$this->checkKey();
		if (apc_clear_cache('opcode')) {
			echo 'SUCCESS';
		} else {
			echo 'ERROR';
		}
	}

	protected function checkKey() {
		if ($this->getRequest()->getParam('key') != Mage::helper('core')->encrypt('secret')) {
			Mage::throwException('Wrong secret key!');
		}
	}

}