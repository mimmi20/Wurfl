<?php

namespace WurflTest\Handlers\Normalizer;

use UaNormalizer\Generic\BlackBerry;

/**
 * Class BlackBerryTest
 *
 * @group Handlers
 */
class BlackBerryTest extends TestBase
{
    protected function setUp()
    {
        $this->normalizer = new BlackBerry();
    }

    /**
     * @test
     * @dataProvider blackberryUserAgentsDataProvider
     *
     * @param string $userAgent
     * @param string $expected
     */
    public function shouldRemoveAllCharactersBeforeTheLastBlackberryString($userAgent, $expected)
    {
        $found = $this->normalizer->normalize($userAgent);
        self::assertEquals($expected, $found);
    }

    public function blackberryUserAgentsDataProvider()
    {
        return array(
            array(
                'Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.135 Mobile Safari/534.1+',
                'BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.135 Mobile Safari/534.1+',
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0) BlackBerry8800/4.2.1 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/134',
                'BlackBerry8800/4.2.1 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/134',
            ),
            array('BlackBerry', 'BlackBerry'),
        );
    }
}
