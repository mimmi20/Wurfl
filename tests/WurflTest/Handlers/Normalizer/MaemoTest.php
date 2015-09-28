<?php
namespace WurflTest\Handlers\Normalizer;

use Wurfl\Handlers\Normalizer\Specific\Maemo;
use Wurfl\WurflConstants;

/**
 *  test case.
 */
class MaemoTest extends TestBase
{

    protected function setUp()
    {
        $this->normalizer = new Maemo();
    }

    /**
     * @test
     * @dataProvider maemoUserAgentsDataProvider
     *
     * @param string $userAgent
     * @param string $expected
     */
    public function shoudReturnTheStringAfterMaemo($userAgent, $expected)
    {
        $found = $this->normalizer->normalize($userAgent);
        self::assertEquals($expected, $found);
    }

    public function maemoUserAgentsDataProvider()
    {
        return array(
            array(
                'Mozilla/5.0 (X11; U; Linux armv7l; en-GB; rv:1.9.2.3pre) Gecko/20100624 Firefox/3.5 Maemo Browser 1.7.4.8 RX-51 N900',
                'Maemo RX-51 N900' . WurflConstants::RIS_DELIMITER . 'Mozilla/5.0 (X11; U; Linux armv7l; en-GB; rv:1.9.2.3pre) Gecko/20100624 Firefox/3.5 Maemo Browser 1.7.4.8 RX-51 N900'
            ),
            array('Mozilla', 'Mozilla'),
            array(
                'Maemo Browser 1.7.4.8 RX-51 N900',
                'Maemo RX-51 N900' . WurflConstants::RIS_DELIMITER . 'Maemo Browser 1.7.4.8 RX-51 N900'
            )

        );
    }
}
