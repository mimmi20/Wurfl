<?php
/**
 * test case
 */
require_once 'Cache/APCCacheProviderTest.php';
require_once 'Cache/FileCacheProviderTest.php';

/**
 * Static test suite.
 */
class WURFL_CacheTestSuite extends PHPUnit_Framework_TestSuite {
	
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'Cache Test Suite' );
		$this->addTestSuite ( 'WURFL_Cache_APCCacheProviderTest' );
		$this->addTestSuite ( 'WURFL_Cache_FileCacheProviderTest' );
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

