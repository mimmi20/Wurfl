<?php
/**
 * test case
 */

require_once 'BaseTest.php';

/**
 *  test case.
 */
class WURFL_Request_UserAgentNormalizer_MSIETest extends WURFL_Request_UserAgentNormalizer_BaseTest {

	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		$this->normalizer = new \Wurfl\Request\UserAgentNormalizer\Specific\MSIE();
	}
	
	
	/**
	 * @test
	 * @dataProvider msieUserAgentsDataProvider
	 *
	 */
	function shoudRemoveAllTheCharactersAfterTheMinorVersion($userAgent, $expected) {
		$this->assertNormalizeEqualsExpected($userAgent, $expected);			
	}
		
	
	
	function msieUserAgentsDataProvider() {
		return array(
			    array("Mozilla/2.0 (compatible; MSIE 3.02; Windows CE; Smartphone; 176x220)", "MSIE 3.0"),
                array("Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; Smartphone; 176x220)", "MSIE 4.0"),
                array("MSIE 3.x", "MSIE 3.x"),
                array("Mozilla", "Mozilla"),
                array("Firefox", "Firefox")
		
		);
	}
	
	

}

