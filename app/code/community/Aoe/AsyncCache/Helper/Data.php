<?php

/**
 * Cache cleaner helper
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Prints the trail (for debugging purposes)
	 * Taken from TYPO3 :)
	 *
	 * @return string
	 */
	public function debugTrail() {
		$trail = debug_backtrace();
		$trail = array_reverse($trail);
		array_pop($trail);

		$path = array();
		foreach ($trail as $dat) {
		    $tmp = '';
		    $tmp .= isset($dat['class']) ? $dat['class'] : '';
		    $tmp .= isset($dat['type']) ? $dat['type'] : '';
		    $tmp .= isset($dat['function']) ? $dat['function'] : '';
		    $tmp .= '#';
		    $tmp .= isset($dat['line']) ? $dat['line'] : '';
            $path[] = $tmp;
		}

		return implode(' // ', $path);
	}

}
