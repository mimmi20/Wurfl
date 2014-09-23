<?php
namespace WurflTest;

use Wurfl\Configuration\Config;
use Wurfl\Configuration\InMemoryConfig;
use Wurfl\Manager;
use WurflCache\Adapter\Memory;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Wurfl\Manager
     */
    private static $wurflManager;

    const RESOURCES_DIR     = "../resources";
    const WURFL_CONFIG_FILE = "../resources/wurfl-config.xml";
    const CACHE_DIR         = "../resources/cache";

    /**
     * @var \Wurfl\Manager
     */
    private $object = null;
    
    public static function setUpBeforeClass()
    {
        self::$wurflManager = self::initManager();
    }
    
    /**
     * return \Wurfl\Manager
     */
    private static function initManager()
    {
        $resourcesDir = __DIR__ . DIRECTORY_SEPARATOR . self::RESOURCES_DIR;
        $cacheDir     = __DIR__ . DIRECTORY_SEPARATOR . self::CACHE_DIR;
        $config       = new InMemoryConfig();

        $config->wurflFile($resourcesDir . "/wurfl.xml");

        $params = array(
            Config::DIR        => $cacheDir,
            Config::EXPIRATION => 0
        );
        $config->persistence('file', $params);
        $persistenceStorage = new Memory($params);
        return new Manager($config, $persistenceStorage, $persistenceStorage);
    }

    public static function tearDownAfterClass()
    {
        // FIXME: tear down is happening before tests are finished
        //self::$persistenceStorage->clear();
    }

    public function setUp()
    {
        $this->object = self::initManager();
    }
    
    public function testShouldReturnGenericForEmptyUserAgent()
    {
        $deviceFound = $this->object->getDeviceForUserAgent('');
        self::assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnGenericForNullUserAgent()
    {
        $deviceFound = $this->object->getDeviceForUserAgent(null);
        self::assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnAllDevicesId()
    {
        $devicesId = $this->object->getAllDevicesID();
        self::assertContains("generic", $devicesId);
    }

    public function testShouldReturnWurflVersionInfo()
    {
        $wurflInfo = $this->object->getWURFLInfo();
        self::assertEquals("Wireless Universal Resource File v_2.1.0.1", $wurflInfo->version);
        self::assertEquals("July 30, 2007", $wurflInfo->lastUpdated);
    }

    public function testGetListOfGroups()
    {
        $actualGroups = array(
            "product_info",
            "wml_ui",
            "chtml_ui",
            "xhtml_ui",
            "markup",
            "cache",
            "display",
            "image_format"
        );
        $listOfGroups = $this->object->getListOfGroups();
        foreach ($actualGroups as $groupId) {
            self::assertContains($groupId, $listOfGroups);
        }
    }

    /**
     *
     * @dataProvider groupIdCapabilitiesNameProvider
     */
    public function testGetCapabilitiesNameForGroup($groupId, $capabilitiesName)
    {
        $capabilities = $this->object->getCapabilitiesNameForGroup($groupId);
        self::assertEquals($capabilitiesName, $capabilities);
    }

    /**
     *
     * @dataProvider fallBackDevicesIdProvider
     */
    public function testGetFallBackDevices($deviceId, $expected)
    {
        $fallBackDevices = $this->object->getFallBackDevices($deviceId);

        self::assertSame($expected, $fallBackDevices);
    }

    private function deviceId($device)
    {
        return $device->id;
    }

    /**
     *
     */
    public function fallBackDevicesIdProvider()
    {
        return array(
            array("blackberry_generic_ver2", array("blackberry_generic", "generic_xhtml", "generic"))
        );
    }

    /**
     *
     */
    public static function groupIdCapabilitiesNameProvider()
    {
        return array(
            array(
                "chtml_ui",
                array(
                    "chtml_display_accesskey",
                    "emoji",
                    "chtml_can_display_images_and_text_on_same_line",
                    "chtml_displays_image_in_center",
                    "imode_region",
                    "chtml_make_phone_call_string",
                    "chtml_table_support"
                )
            )
        );
    }
    
    /**
     *
     * @dataProvider deviceIdAgentProvider
     */
    public function testDeviceIdForRequest($userAgent, $expectedDeviceId)
    {
        $class = new ReflectionClass('\\Wurfl\\Manager');
        $method = $class->getMethod('deviceIdForRequest');
        $method->setAccessible(true);
        
        $header    = array(
            'HTTP_USER_AGENT' => $userAgent
        );
        $request = new GenericRequest($header, $userAgent, null, false);
        
        $deviceId = $method->invoke($this->object, $request);

        self::assertSame($expectedDeviceId, $deviceId);
    }
    
    public function deviceIdAgentProvider()
    {
        return array(
            array('Mozilla/5.0 (compatible; OpenWeb 5.7.2.3-02; ms-office; MSOffice 14) Opera 8.54', 'opera_8'),
            array('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Windows Phone 6.5; garmin-asus-Nuvifone-M10/1.0)', 'generic_ms_winmo6_5'),
            array('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Vodafone/1.0/HTC_HD_mini/1.11.162.1 (87652); Windows Phone 6.5.3.5)', 'generic_ms_winmo6_5'),
            array('Mozilla/5.0 (PlayStation 4 1.70) AppleWebKit/536.26 (KHTML, like Gecko)', 'sony_playstation4_ver1'),
            array('Mozilla/5.0 (X11; FreeBSD amd64; rv:23.0) Gecko/20100101 Firefox/23.0', 'firefox_23_0'),
            array('Mozilla/5.0 (X11; U; FreeBSD i386; pl; rv:1.8.1.12) Gecko/20080213 Epiphany/2.20 Firefox/2.0.0.12', 'firefox_2_0'),
            array('Mozilla/5.0 (Randomized by FreeSafeIP.com/upgrade-to-remove; compatible; MSIE 8.0; Windows NT 5.0) Chrome/21.0.1229.79', 'google_chrome_21'),
            array('Mozilla/5.0 (X11; CrOS armv7l 2913.260.0) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.99 Safari/537.11', 'chrome_book_ver1'),
            array('Mozilla/5.0 (compatible; Windows NT 5.1; WOW64) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/19.0.1084.36 Safari/535.19', 'google_chrome_19'),
            array('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; HTC_HD2_T8585; Windows Phone 6.5)', 'htc_hd2_ver1_subwp65'),
            array('Mozilla/5.0 (Linux; U; de-de; GT-P1000 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1', 'samsung_gt_i9100_ver1_funnyua'),
            array('Mozilla/5.0 (Linux; U; de-de; GT-S7500 Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Safari/533.1', 'samsung_gt_s5830_ver1_suban22_funnyua'),
            array('Mozilla/5.0 (PLAYSTATION 3 4.20) AppleWebKit/531.22.8 (KHTML, like Gecko)', 'sony_playstation3_ver1_subua45'),
            array('Mozilla/5.0 (PlayStation 4 1.52) AppleWebKit/536.26 (KHTML, like Gecko)', 'sony_playstation4_ver1_subua151'),
            array('Mozilla/5.0 (Linux; U; en-us; EBRD1201; EXT) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1', 'sony_prst1_ver1'),
            array('Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.0.1; en-US) AppleWebKit/535.8+ (KHTML, like Gecko) Version/7.2.0.1 Safari/535.8+', 'rim_playbook_ver1_subos2'),
        );
    }
}
