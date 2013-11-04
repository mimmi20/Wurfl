<?php
/**
 * test case
 */
require_once 'TestUtils.php';

class WURFL_WURFLManagerTest extends PHPUnit_Framework_TestCase {

    protected static $wurflManager;

    const RESOURCES_DIR = "../resources";
    const WURFL_CONFIG_FILE = "../resources/wurfl-config.xml";
    const CACHE_DIR = "../resources/cache";

    private static $wurflManagerFactory;

    private static $persistenceStorage;

    
    public static function setUpBeforeClass() {
        self::createWurflManger();
    }

    public static function tearDownAfterClass() {
        // FIXME: tear down is happening before tests are finished 
        //self::$persistenceStorage->clear();
    }

    public static function createWurflManger() {
        $resourcesDir = __DIR__ . DIRECTORY_SEPARATOR . self::RESOURCES_DIR;
        $cacheDir = __DIR__ . DIRECTORY_SEPARATOR . self::CACHE_DIR;
        $config = new \Wurfl\Configuration\InMemoryConfig();

        $config->wurflFile($resourcesDir . "/wurfl-regression.xml")
                ->wurflPatch($resourcesDir . "/web_browsers_patch.xml")
                ->wurflPatch($resourcesDir . "/spv_patch.xml")
                ->wurflPatch($resourcesDir . "/android_patch.xml")
                ->wurflPatch($resourcesDir . "/new_devices.xml");

        $params = array(
            "dir" => $cacheDir,
            \Wurfl\Configuration\Config::EXPIRATION => 0);
        $config->persistence("file", $params);
        self::$persistenceStorage = new \Wurfl\Storage\Memory($params);
        self::$wurflManagerFactory = new \Wurfl\ManagerFactory ($config, self::$persistenceStorage);
        self::$wurflManager = self::$wurflManagerFactory->create();

    }

    public function testShouldReturnGenericForEmptyUserAgent() {
        $deviceFound = self::$wurflManager->getDeviceForUserAgent('');
        $this->assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnGenericForNullUserAgent() {
        $deviceFound = self::$wurflManager->getDeviceForUserAgent(NULL);
        $this->assertEquals('generic', $deviceFound->id);
    }

    public function testShouldReturnAllDevicesId() {
        $devicesId = self::$wurflManager->getAllDevicesID();
        $this->assertContains("generic", $devicesId);
    }

    public function testShouldReturnWurflVersionInfo() {
        $wurflInfo = self::$wurflManager->getWURFLInfo();
        $this->assertEquals("Wireless Universal Resource File v_2.1.0.1", $wurflInfo->version);
        $this->assertEquals("July 30, 2007", $wurflInfo->lastUpdated);

    }

    public function testGetListOfGroups() {
        $actualGroups = array("product_info", "wml_ui", "chtml_ui", "xhtml_ui", "markup", "cache", "display", "image_format");
        $listOfGroups = self::$wurflManager->getListOfGroups();
        foreach ($actualGroups as $groupId) {
            $this->assertContains($groupId, $listOfGroups);
        }
    }

    /**
     *
     * @dataProvider groupIdCapabilitiesNameProvider
     */
    public function testGetCapabilitiesNameForGroup($groupId, $capabilitiesName) {
        $capabilities = self::$wurflManager->getCapabilitiesNameForGroup($groupId);
        $this->assertEquals($capabilitiesName, $capabilities);
    }

    /**
     *
     * @dataProvider fallBackDevicesIdProvider
     */
    public function testGetFallBackDevices($deviceId, $fallBacksId) {
        $fallBackDevices = self::$wurflManager->getFallBackDevices($deviceId);
        return array_map(array($this, 'deviceId'), $fallBackDevices);
    }

    private function deviceId($device) {
        return $device->id;
    }
    
    /**
     *
     * @dataProvider devicesFallBackProvider
     */
    public function fallBackDevicesIdProvider($deviceId) {
        return array(array("blackberry_generic_ver2", array("blackberry_generic", "generic_xhtml", "generic")));

    }

    public static function groupIdCapabilitiesNameProvider() {
        return array(array("chtml_ui", array("chtml_display_accesskey", "emoji", "chtml_can_display_images_and_text_on_same_line", "chtml_displays_image_in_center", "imode_region", "chtml_make_phone_call_string", "chtml_table_support")));
    }
}
