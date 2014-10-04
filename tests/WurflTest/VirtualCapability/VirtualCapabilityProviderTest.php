<?php
namespace WurflTest\VirtualCapability;

use Wurfl\Configuration\Config;
use Wurfl\Configuration\InMemoryConfig;
use Wurfl\CustomDevice;
use Wurfl\DeviceRepositoryBuilder;
use Wurfl\VirtualCapability\VirtualCapabilityProvider;
use Wurfl\Request\GenericRequest;
use WurflCache\Adapter\Memory;
use WurflCache\Adapter\File;
use Wurfl\Storage\Storage;
use Wurfl\UserAgentHandlerChainFactory;
use Wurfl\Xml\DevicePatcher;

class VirtualCapabilityProviderTest
    extends \PHPUnit_Framework_TestCase
{
    const RESOURCES_DIR     = 'tests/resources/';
    const WURFL_CONFIG_FILE = 'tests/resources/wurfl-config.xml';
    const CACHE_DIR         = 'tests/resources/cache';

    /** @var \Wurfl\DeviceRepositoryBuilder */
    private $deviceRepositoryBuilder;

    protected function setUp()
    {
        $this->markTestSkipped('not implemented yet');
        
        $persistenceProvider           = new Storage(new Memory());
        $userAgentHandlerChain         = UserAgentHandlerChainFactory::createFrom(
            $persistenceProvider,
            $persistenceProvider
        );
        $devicePatcher                 = new DevicePatcher();
        $this->deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $persistenceProvider, $userAgentHandlerChain, $devicePatcher
        );
    }

    /**
     * @param string $userAgent
     * @param string $expectedDeviceId
     * @param string $expectedBrowser
     * @param string $expectedOs
     * @param string $expectedDevice
     *
     * @internal     param $ $
     * @dataProvider deviceIdAgentProvider
     */
    public function testGet($userAgent, $expectedDeviceId, $expectedBrowser = null, $expectedOs = null, $expectedDevice = null)
    {
        $header  = array(
            'HTTP_USER_AGENT' => $userAgent
        );
        $request      = new GenericRequest($header, $userAgent, null, false);

        $deviceRepository = $this->deviceRepositoryBuilder->build(self::RESOURCES_DIR . 'wurfl-regression.xml');
        self::assertNotNull($deviceRepository);

        $modelDevices = $deviceRepository->getDeviceHierarchy($expectedDeviceId);
        $customDevice = new CustomDevice($modelDevices, $request);

        $testObject = new VirtualCapabilityProvider($customDevice, $request);

        self::assertTrue($testObject->exists('advertised_browser'));
        self::assertSame($expectedBrowser, $testObject->get('advertised_browser'));

        self::assertTrue($testObject->exists('advertised_device_os'));
        self::assertSame($expectedOs, $testObject->get('advertised_device_os'));

        self::assertTrue($testObject->exists('complete_device_name'));
        self::assertSame($expectedDevice, $testObject->get('complete_device_name'));
    }

    public function deviceIdAgentProvider()
    {
        return array(
            array('Mozilla/5.0 (compatible; OpenWeb 5.7.2.3-02; ms-office; MSOffice 14) Opera 8.54', 'opera_8'),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Windows Phone 6.5; garmin-asus-Nuvifone-M10/1.0)',
                'generic_ms_winmo6_5'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Vodafone/1.0/HTC_HD_mini/1.11.162.1 (87652); Windows Phone 6.5.3.5)',
                'generic_ms_winmo6_5'
            ),
            array('Mozilla/5.0 (PlayStation 4 1.70) AppleWebKit/536.26 (KHTML, like Gecko)', 'sony_playstation4_ver1'),
            array('Mozilla/5.0 (X11; FreeBSD amd64; rv:23.0) Gecko/20100101 Firefox/23.0', 'firefox_23_0'),
            array(
                'Mozilla/5.0 (X11; U; FreeBSD i386; pl; rv:1.8.1.12) Gecko/20080213 Epiphany/2.20 Firefox/2.0.0.12',
                'firefox_2_0'
            ),
            array(
                'Mozilla/5.0 (Randomized by FreeSafeIP.com/upgrade-to-remove; compatible; MSIE 8.0; Windows NT 5.0) Chrome/21.0.1229.79',
                'google_chrome_21'
            ),
            array(
                'Mozilla/5.0 (X11; CrOS armv7l 2913.260.0) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.99 Safari/537.11',
                'chrome_book_ver1'
            ),
            array(
                'Mozilla/5.0 (compatible; Windows NT 5.1; WOW64) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/19.0.1084.36 Safari/535.19',
                'google_chrome_19'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; HTC_HD2_T8585; Windows Phone 6.5)',
                'htc_hd2_ver1_subwp65'
            ),
            array(
                'Mozilla/5.0 (Linux; U; de-de; GT-P1000 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1',
                'samsung_gt_i9100_ver1_funnyua'
            ),
            array(
                'Mozilla/5.0 (Linux; U; de-de; GT-S7500 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1',
                'samsung_gt_s5830_ver1_suban22_funnyua'
            ),
            array(
                'Mozilla/5.0 (PLAYSTATION 3 4.20) AppleWebKit/531.22.8 (KHTML, like Gecko)',
                'sony_playstation3_ver1_subua45'
            ),
            array(
                'Mozilla/5.0 (PlayStation 4 1.52) AppleWebKit/536.26 (KHTML, like Gecko)',
                'sony_playstation4_ver1_subua151'
            ),
            array(
                'Mozilla/5.0 (Linux; U; en-us; EBRD1201; EXT) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
                'sony_prst1_ver1'
            ),
            array(
                'Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.0.1; en-US) AppleWebKit/535.8+ (KHTML, like Gecko) Version/7.2.0.1 Safari/535.8+',
                'rim_playbook_ver1_subos2'
            ),
            array(
                'Mozilla/5.0 (PLAYSTATION 3 4.31) AppleWebKit/531.22.8 (KHTML, like Gecko)',
                'sony_playstation3_ver1_subua45'
            ),
            array(
                'Mozilla/5.0 (Linux; U; de-de; GT-I9100 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1',
                'samsung_gt_i9100_ver1_funnyua'
            ),
            array(
                'Mozilla/5.0 (PLAYSTATION 3 4.46) AppleWebKit/531.22.8 (KHTML, like Gecko)',
                'sony_playstation3_ver1_subua45'
            ),
            array(
                'Mozilla/5.0 (PLAYSTATION 3 4.25) AppleWebKit/531.22.8 (KHTML, like Gecko)',
                'sony_playstation3_ver1_subua45'
            ),
            array(
                'Mozilla/5.0 (Windows NT 6.4; Trident/7.0; rv:11.0) like Gecko',
                'msie_11'
            ),
            array(
                'Mozilla/5.0 (Linux; U; de-de; GT-I9001 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1',
                'samsung_gt_i9100_ver1_funnyua'
            ),
            array(
                'Mozilla/5.0 (Linux; U; de-de; GT-N7000 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1',
                'samsung_gt_i9100_ver1_funnyua'
            ),
            array(
                'Mozilla/5.0 (Windows NT 6.4; Win64; x64; Trident/7.0; rv:11.0) like Gecko',
                'msie_11'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Vodafone/1.0/HTC_HD2/1.72.162.0 (82124); Windows Phone 6.5)',
                'htc_hd2_ver1_subuavoda'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Windows Phone 6.5 HTC_HD2/1.0)',
                'htc_hd2_ver1_subie52'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Windows Phone 6.5)',
                'generic_ms_winmo6_5'
            ),
            array(
                'Mozilla/5.0 (X11; FreeBSD amd64; rv:16.0) Gecko/20100101 Firefox/16.0',
                'firefox_16_0'
            ),
            array(
                'Mozilla/5.0 (X11; U; FreeBSD i386; de-DE; rv:1.9.0.5) Gecko/2009012218 Firefox/3.0.5',
                'firefox_3_0'
            ),
            array(
                'Mozilla/5.0 (Windows NT 6.4; Trident/8.0; rv:550) AppleWebKit/537.36 (KHTML, like Gecko) Version/7.0 Safari/550.1.3',
                'generic_web_browser'
            ),
            array(
                'Mozilla/5.0 (en)',
                'mozilla_ver5'
            ),
            array(
                'Opera/9.80 (X11; FreeBSD 9.0-RELEASE amd64; U; ru) Presto/2.10.289 Version/12.00',
                'opera_12'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Vodafone/1.0/HTC_HD2/3.14.162.5 (04666); Windows Phone 6.5)',
                'generic_ms_winmo6_5'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Vodafone/1.0/HTC_HD_mini/1.41.162.1 (10904); Windows Phone 6.5.3.5)',
                'generic_ms_winmo6_5'
            ),
            array(
                'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Windows Phone 6.5.3.5; Windows Phone 6.5; SonyEricssonM1i/R1AA; Profile/MIDP-2.1; Configuration/CLDC-1.1)',
                'sonyericsson_m1i_ver1_sub65phone'
            ),
            array(
                'Mozilla/5.0 (X11; CrOS armv7l 2913.187.0) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.89 Safari/537.11',
                'chrome_book_ver1'
            ),
        );
    }
}
