<?php
/**
 * test case
 */

require_once 'BaseTest.php';

/**
 *  test case.
 */
class WURFL_Request_UserAgentNormalizer_ChromeTest extends WURFL_Request_UserAgentNormalizer_BaseTest
{

    const CHROME_USERAGENTS_FILE = "chrome.txt";

    function setUp()
    {
        $this->normalizer = new \Wurfl\Request\Normalizer\Specific\Chrome();
    }

    /**
     * @test
     * @dataProvider chromeUserAgentsDataProvider
     *
     */
    function shoudReturnOnlyFirefoxStringWithTheMajorVersion($userAgent, $expected)
    {
        $this->assertNormalizeEqualsExpected($userAgent, $expected);
    }

    function chromeUserAgentsDataProvider()
    {
        return array(
            array(
                @"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13"
            ,
                "Chrome/0"
            ),
            array("Chrome/9.x", "Chrome/9"),
            array("Mozilla", "Mozilla"),
            array("Chrome", "Chrome")

        );
    }
}

