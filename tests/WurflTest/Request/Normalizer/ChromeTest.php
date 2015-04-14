<?php
namespace WurflTest\Request\Normalizer;

use Wurfl\Request\Normalizer\Specific\Chrome;

/**
 *  test case.
 */
class ChromeTest
    extends TestBase
{

    const CHROME_USERAGENTS_FILE = 'chrome.txt';

    protected function setUp()
    {
        $this->normalizer = new Chrome();
    }

    /**
     * @test
     * @dataProvider chromeUserAgentsDataProvider
     *
     */
    public function shoudReturnOnlyFirefoxStringWithTheMajorVersion($userAgent, $expected)
    {
        $this->assertNormalizeEqualsExpected($userAgent, $expected);
    }

    public function chromeUserAgentsDataProvider()
    {
        return array(
            array(
                @'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13',
                'Chrome/0'
            ),
            array('Chrome/9.x', 'Chrome/9'),
            array('Mozilla', 'Mozilla'),
            array('Chrome', 'Chrome')

        );
    }
}
