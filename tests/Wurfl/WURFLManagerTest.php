<?php
use Wurfl\Configuration\Config;
use Wurfl\Configuration\InMemoryConfig;
use Wurfl\Manager;
use Wurfl\Storage\Memory;

/**
 * test case
 */
require_once 'TestUtils.php';

class WURFL_WURFLManagerTest extends PHPUnit_Framework_TestCase
{
    /** @var  Manager */
    protected static $wurflManager;

    const RESOURCES_DIR     = "../resources";
    const WURFL_CONFIG_FILE = "../resources/wurfl-config.xml";
    const CACHE_DIR         = "../resources/cache";

    private static $persistenceStorage;

    public static function setUpBeforeClass()
    {
        $resourcesDir = __DIR__ . DIRECTORY_SEPARATOR . self::RESOURCES_DIR;
        $cacheDir     = __DIR__ . DIRECTORY_SEPARATOR . self::CACHE_DIR;
        $config       = new InMemoryConfig();

        $config->wurflFile($resourcesDir . "/wurfl-regression.xml")
            ->wurflPatch($resourcesDir . "/web_browsers_patch.xml")
            ->wurflPatch($resourcesDir . "/spv_patch.xml")
            ->wurflPatch($resourcesDir . "/android_patch.xml")
            ->wurflPatch($resourcesDir . "/new_devices.xml");

        $params = array(
            Config::DIR        => $cacheDir,
            Config::EXPIRATION => 0
        );
        $config->persistence('file', $params);
        self::$persistenceStorage = new Memory($params);
        $wurflManager             = new \Wurfl\Manager($config, self::$persistenceStorage, self::$persistenceStorage);
        self::$wurflManager = $wurflManager;
    }

    public static function tearDownAfterClass()
    {
        // FIXME: tear down is happening before tests are finished 
        //self::$persistenceStorage->clear();
    }

    public function testShouldReturnGenericForEmptyUserAgent()
    {
        $deviceFound = self::$wurflManager->getDeviceForUserAgent('');
        self::assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnGenericForNullUserAgent()
    {
        $deviceFound = self::$wurflManager->getDeviceForUserAgent(null);
        self::assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnAllDevicesId()
    {
        $devicesId = self::$wurflManager->getAllDevicesID();
        self::assertContains("generic", $devicesId);
    }

    public function testShouldReturnWurflVersionInfo()
    {
        $wurflInfo = self::$wurflManager->getWURFLInfo();
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
        $listOfGroups = self::$wurflManager->getListOfGroups();
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
        $capabilities = self::$wurflManager->getCapabilitiesNameForGroup($groupId);
        self::assertEquals($capabilitiesName, $capabilities);
    }

    /**
     *
     * @dataProvider fallBackDevicesIdProvider
     */
    public function testGetFallBackDevices($deviceId)
    {
        $fallBackDevices = self::$wurflManager->getFallBackDevices($deviceId);

        return array_map(array($this, 'deviceId'), $fallBackDevices);
    }

    private function deviceId($device)
    {
        return $device->id;
    }

    /**
     *
     * @dataProvider devicesFallBackProvider
     */
    public function fallBackDevicesIdProvider()
    {
        return array(array("blackberry_generic_ver2", array("blackberry_generic", "generic_xhtml", "generic")));
    }

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
}
