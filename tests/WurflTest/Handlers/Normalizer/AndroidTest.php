<?php

namespace WurflTest\Handlers\Normalizer;

/*
 * test case
 */
use Wurfl\Handlers\Normalizer\Generic\LocaleRemover;
use Wurfl\Handlers\Normalizer\Specific\Android;
use Wurfl\WurflConstants;

/**
 * test case.
 */
class AndroidTest extends TestBase
{
    protected function setUp()
    {
        $this->normalizer = new Android();
    }

    /**
     * @test
     * @dataProvider normalizerDataProvider
     *
     * @param string $userAgent
     * @param string $expected
     */
    public function testtrimsToTwoDigitTheOsVersion($userAgent, $expected)
    {
        // Locale must be normalized before Android normalizer can be run
        $localeNormalizer = new LocaleRemover();
        $found            = $this->normalizer->normalize($localeNormalizer->normalize($userAgent));
        self::assertEquals($expected, $found);
    }

    public function normalizerDataProvider()
    {
        return array(
            array('FOO', 'FOO'),
            array(
                'Mozilla/5.0 (Linux; U; Android 1.0.15; fr-fr; A70HB Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',
                '1.0 A70HB' . WurflConstants::RIS_DELIMITER . 'Mozilla/5.0 (Linux; U; Android 1.0; xx-xx; A70HB Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',
            ),
            array(
                'Mozilla/5.0 (Linux; U; Android 2.1-update1; en-us; Hero Build/ERE27) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',
                '2.1 Hero' . WurflConstants::RIS_DELIMITER . 'Mozilla/5.0 (Linux; U; Android 2.1; xx-xx; Hero Build/ERE27) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2',
            ),
            array(
                'Mozilla/5.0 (Linux; U; Android 2.2.1; en-us; myTouchHD Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
                '2.2 myTouchHD' . WurflConstants::RIS_DELIMITER . 'Mozilla/5.0 (Linux; U; Android 2.2; xx-xx; myTouchHD Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            ),
        );
    }
}
