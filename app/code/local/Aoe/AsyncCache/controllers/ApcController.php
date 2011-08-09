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
		var_dump($mem);
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

	/**
	 * @see http://code.google.com/p/munin-php-apc/
	 */
	public function muninAction() {
		if (function_exists("apc_cache_info") && function_exists("apc_sma_info")) {
			$time = time();
			$mem = apc_sma_info();
			$mem_size = $mem['num_seg'] * $mem['seg_size'];
			$mem_avail = $mem['avail_mem'];
			$mem_used = $mem_size - $mem_avail;
			// Some code taken from the file apc.php by The PHP Group.
			$nseg = $freeseg = $fragsize = $freetotal = 0;
			for ($i = 0; $i < $mem['num_seg']; $i ++) {
				$ptr = 0;
				foreach ($mem['block_lists'][$i] as $block) {
					if ($block['offset'] != $ptr) {
						++ $nseg;
					}
					$ptr = $block['offset'] + $block['size'];
					// Only consider blocks <5M for the fragmentation %
					if ($block['size'] < (5 * 1024 * 1024))
						$fragsize += $block['size'];
					$freetotal += $block['size'];
				}
				$freeseg += count($mem['block_lists'][$i]);
			}
			if ($freeseg < 2) {
				$fragsize = 0;
				$freeseg = 0;
			}
			$cache_mode = 'opmode';
			$cache = @apc_cache_info($cache_mode);
			// Item hits, misses and inserts
			$hits = $cache['num_hits'];
			$misses = $cache['num_misses'];
			$inserts = $cache['num_inserts'];
			// rates
			$req_rate = ($cache['num_hits'] + $cache['num_misses']) / ($time - $cache['start_time']);
			$hit_rate = ($cache['num_hits']) / ($time - $cache['start_time']); // Number of entries in cache $number_entries = $cache['num_entries'];
			$miss_rate = ($cache['num_misses']) / ($time - $cache['start_time']); // Total number of cache purges $purges = $cache['expunges'];
			$insert_rate = ($cache['num_inserts']) / ($time - $cache['start_time']);
			// Number of entries in cache
			$number_entries = $cache['num_entries'];
			// Total number of cache purges
			$purges = $cache['expunges'];
			//apc_clear_cache($cache_mode);
			$out = array(
				'size: ' . sprintf("%.2f", $mem_size), 'used: ' . sprintf("%.2f", $mem_used), 'free: ' . sprintf("%.2f", $mem_avail - $fragsize), 'hits: ' . sprintf("%.2f", $hits * 100 / ($hits + $misses)), 'misses: ' . sprintf("%.2f", $misses * 100 / ($hits + $misses)), 'request_rate: ' . sprintf("%.2f", $req_rate), 'hit_rate: ' . sprintf("%.2f", $hit_rate), 'miss_rate: ' . sprintf("%.2f", $miss_rate), 'insert_rate: ' . sprintf("%.2f", $insert_rate), 'entries: ' . $number_entries, 'inserts: ' . $inserts, 'purges: ' . $purges, // TODO: Delete
				'purge_rate: ' . sprintf("%.2f", (100 - ($number_entries / $inserts) * 100)), // TODO: Delete
				'fragment_percentage: ' . sprintf("%.2f", ($fragsize / $mem_avail) * 100), 'fragmented: ' . sprintf("%.2f", $fragsize), 'fragment_segments: ' . $freeseg
			);
		} else {
			$out = array('APC-not-installed');
		}
		echo implode(' ', $out);
	}

	protected function checkKey() {
		if ($this->getRequest()->getParam('key') != Mage::helper('core')->encrypt('secret')) {
			Mage::throwException('Wrong secret key!');
		}
	}

}