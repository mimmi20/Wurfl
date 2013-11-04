<?php
/**
 * test case
 */


require_once 'BaseTest.php';

/**
 *  test case.
 */
class WURFL_Request_UserAgentNormalizer_MaemoTest extends WURFL_Request_UserAgentNormalizer_BaseTest  {
        

    function setUp() {        
        $this->normalizer = new \Wurfl\Request\UserAgentNormalizer\Specific\Maemo();
    }
    

    /**
     * @test
     * @dataProvider maemoUserAgentsDataProvider
     *
     */
    function shoudReturnTheStringAfterMaemo($userAgent, $expected) {
        $found = $this->normalizer->normalize($userAgent);
        $this->assertEquals($found, $expected);
    
    }
        
    
    function maemoUserAgentsDataProvider() {
        return array(
                array("Mozilla/5.0 (X11; U; Linux armv7l; en-GB; rv:1.9.2.3pre) Gecko/20100624 Firefox/3.5 Maemo Browser 1.7.4.8 RX-51 N900", "Maemo RX-51 N900".\Wurfl\Constants::RIS_DELIMITER."Mozilla/5.0 (X11; U; Linux armv7l; en-GB; rv:1.9.2.3pre) Gecko/20100624 Firefox/3.5 Maemo Browser 1.7.4.8 RX-51 N900"),
                array("Mozilla", "Mozilla"),
                array("Maemo Browser 1.7.4.8 RX-51 N900", "Maemo RX-51 N900".\Wurfl\Constants::RIS_DELIMITER."Maemo Browser 1.7.4.8 RX-51 N900")
 
        );    
    }
        
        
}

