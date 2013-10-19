<?php
/**
 * test case
 */
require_once 'UserAgentNormalizerTest.php';
require_once 'UserAgentNormalizer/LocaleRemoverTest.php';
require_once 'UserAgentNormalizer/BlackBerryTest.php';
require_once 'UserAgentNormalizer/ChromeTest.php';
require_once 'UserAgentNormalizer/FirefoxTest.php';
require_once 'UserAgentNormalizer/MSIETest.php';
require_once 'UserAgentNormalizer/MaemoTest.php';
require_once 'UserAgentNormalizer/AndroidTest.php';

require_once 'UserAgentNormalizer/SerialNumbersTest.php';
require_once 'UserAgentNormalizer/NovarraGoogleTranslatorTest.php';

/**
 * Static test suite.
 */
class WURFL_Request_UserAgentNormalizerTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'UserAgentNormalizerSuite' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizerTest' );
        $this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_AndroidTest' );
        $this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_LocaleRemoverTest' );		        
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_BlackBerryTest' );		
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_ChromeTest' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_FirefoxTest' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_MSIETest' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_MaemoTest' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_SerialNumbersTest' );
		$this->addTestSuite ( 'WURFL_Request_UserAgentNormalizer_NovarraGoogleTranslatorTest' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

