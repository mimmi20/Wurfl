<?php
/**
 * test case
 */
require_once 'Reloader/DefaultWURFLReloaderTest.php';

/**
 * Static test suite.
 */
class WURFL_ReloaderTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'WURFLReloaderTestSuite' );		
		$this->addTestSuite ( 'WURFL_Reloader_DefaultWURFLReloaderTest' );
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ( );
	}
}

