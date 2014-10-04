<?php
namespace WurflTest\Request\Normalizer;

use Wurfl\Request\Normalizer\Generic\NovarraGoogleTranslator;

/**
 *  test case.
 */
class NovarraGoogleTranslatorTest
    extends TestBase
{

    protected function setUp()
    {
        $this->normalizer = new NovarraGoogleTranslator();
    }

    /**
     * @test
     * @dataProvider novarraGoogleTranslatorDataProvider
     *
     */
    public function testNovarraAndGoogleTranslator($userAgent, $expected)
    {
        $found = $this->normalizer->normalize($userAgent);
        self::assertEquals($expected, $found);
    }

    public function novarraGoogleTranslatorDataProvider()
    {
        return array(
            array(
                "BlackBerry8310/4.2.2 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/125 Novarra-Vision/7.3",
                "BlackBerry8310/4.2.2 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/125"
            ),
            array(
                "Palm750/v0100 Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; PPC; 240x320),gzip(gfe) (via translate.google.com)",
                "Palm750/v0100 Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; PPC; 240x320)"
            ),
            array(
                "Nokia3120classic/2.0 (10.00) Profile/MIDP-2.1 Configuration/CLDC-1.1,gzip(gfe) (via translate.google.com)",
                "Nokia3120classic/2.0 (10.00) Profile/MIDP-2.1 Configuration/CLDC-1.1"
            )
        );
    }
}
