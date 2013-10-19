<?php
/**
 * test case
 */
require_once 'Configuration/ArrayConfigTest.php';
require_once 'Configuration/XmlConfigTest.php';
require_once 'Configuration/InMemoryConfigTest.php';

/**
 * Static test suite.
 */
class WURFL_ConfigurationTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'ConfigurationTestSuite' );		
		$this->addTestSuite ( '\Wurfl\Configuration\XmlConfigTest' );
		$this->addTestSuite ( '\Wurfl\Configuration\ArrayConfigTest' );
        $this->addTestSuite ( '\Wurfl\Configuration\InMemoryConfigTest' );
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ( );
	}
}

