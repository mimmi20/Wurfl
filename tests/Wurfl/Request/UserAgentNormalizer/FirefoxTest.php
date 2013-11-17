<?php
/**
 * test case
 */

require_once 'BaseTest.php';

/**
 * test case.
 */
class WURFL_Request_UserAgentNormalizer_FirefoxTest extends WURFL_Request_UserAgentNormalizer_BaseTest
{

    function setUp()
    {
        $this->normalizer = new \Wurfl\Request\UserAgentNormalizer\Specific\Firefox ();
    }

    /**
     * @test
     * @dataProvider firefoxUserAgentsDataProvider
     *
     */
    function shoudReturnOnlyFirefoxStringWithTheMajorVersion($userAgent, $expected)
    {
        $found = $this->normalizer->normalize($userAgent);
        self::assertEquals($found, $expected);
    }

    function firefoxUserAgentsDataProvider()
    {
        return array(
            array(
                "Mozilla/5.0 (X11; U; Linux armv6l; en-US; rv:1.9a6pre) Gecko/20070810 Firefox/3.0a1", "Firefox/3.0a1"
            ),
            array("Firefox/3.x", "Firefox/3.x"),
            array("Mozilla", "Mozilla"),
            array("Firefox", "Firefox")
        );
    }
}

