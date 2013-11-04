<?php
/**
 * test case
 */

require_once 'BaseTest.php';

/**
 *  test case.
 */
class WURFL_Request_UserAgentNormalizer_NovarraGoogleTranslatorTest extends WURFL_Request_UserAgentNormalizer_BaseTest  {
        

    function setUp() {        
        $this->normalizer = new \Wurfl\Request\UserAgentNormalizer\Generic\NovarraGoogleTranslator();
    }
    

    /**
     * @test
     * @dataProvider novarraGoogleTranslatorDataProvider
     *
     */
    function shouldNovarraAndGoogleTranslator($userAgent, $expected) {
        $found = $this->normalizer->normalize($userAgent);
        $this->assertEquals($expected, $found);
    
    }
        
    
    function novarraGoogleTranslatorDataProvider() {
        return array(
                array("BlackBerry8310/4.2.2 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/125 Novarra-Vision/7.3", "BlackBerry8310/4.2.2 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/125"),
                array("Palm750/v0100 Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; PPC; 240x320),gzip(gfe) (via translate.google.com)", "Palm750/v0100 Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; PPC; 240x320)"),
                array("Nokia3120classic/2.0 (10.00) Profile/MIDP-2.1 Configuration/CLDC-1.1,gzip(gfe) (via translate.google.com)", "Nokia3120classic/2.0 (10.00) Profile/MIDP-2.1 Configuration/CLDC-1.1") 
        );    
    }
        
        
}

