<?php

namespace WurflTest\Handlers\Normalizer;

use UaNormalizer\Specific\Firefox;

/**
 * Class FirefoxTest
 *
 * @group Handlers
 */
class FirefoxTest extends TestBase
{
    protected function setUp()
    {
        $this->normalizer = new Firefox();
    }

    /**
     * @test
     * @dataProvider firefoxUserAgentsDataProvider
     *
     * @param string $userAgent
     * @param string $expected
     */
    public function shoudReturnOnlyFirefoxStringWithTheMajorVersion($userAgent, $expected)
    {
        $found = $this->normalizer->normalize($userAgent);
        self::assertEquals($expected, $found);
    }

    public function firefoxUserAgentsDataProvider()
    {
        return array(
            array(
                'Mozilla/5.0 (X11; U; Linux armv6l; en-US; rv:1.9a6pre) Gecko/20070810 Firefox/3.0a1',
                'Firefox/3.0a1',
            ),
            array('Firefox/3.x', 'Firefox/3.x'),
            array('Mozilla', 'Mozilla'),
            array('Firefox', 'Firefox'),
        );
    }
}
