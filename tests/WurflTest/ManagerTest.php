<?php
namespace WurflTest;

use Wurfl\Configuration\Config;
use Wurfl\Configuration\InMemoryConfig;
use Wurfl\Manager;
use Wurfl\Request\GenericRequest;
use Wurfl\Storage\Factory;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    const RESOURCES_DIR     = 'tests/resources/';
    const WURFL_CONFIG_FILE = 'tests/resources/wurfl-config.xml';
    const CACHE_DIR         = 'tests/resources/cache';

    /**
     * @var \Wurfl\Manager
     */
    private $object = null;

    /**
     * @var \Wurfl\Storage\Storage
     */
    private static $cacheStorage = null;

    /**
     * @var \Wurfl\Storage\Storage
     */
    private static $persistenceStorage = null;

    /**
     * @var \Wurfl\Configuration\Config
     */
    private static $config = null;

    public static function setUpBeforeClass()
    {
        $resourcesDir = self::RESOURCES_DIR;
        $cacheDir     = self::CACHE_DIR;
        self::$config = new InMemoryConfig();

        self::$config->wurflFile($resourcesDir . 'wurfl-regression.xml');

        $params = array(
            Config::DIR        => $cacheDir,
            Config::EXPIRATION => 0
        );
        self::$config->persistence('file', $params);
        self::$config->cache('memory');

        self::$cacheStorage       = Factory::create(self::$config->cache);
        self::$persistenceStorage = Factory::create(self::$config->persistence);
    }

    public static function tearDownAfterClass()
    {
        self::$persistenceStorage->clear();
    }

    public function setUp()
    {
        $this->object = new Manager(self::$config, self::$persistenceStorage, self::$cacheStorage);
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
        self::assertContains('generic', $devicesId);
    }

    public function testShouldReturnWurflVersionInfo()
    {
        $wurflInfo = $this->object->getWURFLInfo();
        self::assertEquals('Wireless Universal Resource File v_2.1.0.1', $wurflInfo->version);
        self::assertEquals('Sun Aug 31 00:53:12 -0400 2014', $wurflInfo->lastUpdated);
    }

    public function testGetListOfGroups()
    {
        $actualGroups = array(
            'product_info',
            'wml_ui',
            'chtml_ui',
            'xhtml_ui',
            'markup',
            'cache',
            'display',
            'image_format'
        );
        $listOfGroups = $this->object->getListOfGroups();
        foreach ($actualGroups as $groupId) {
            self::assertContains($groupId, $listOfGroups);
        }
    }

    /**
     *
     * @dataProvider groupIdCapabilitiesNameProvider
     *
     * @param string $groupId
     * @param string $capabilitiesName
     */
    public function testGetCapabilitiesNameForGroup($groupId, $capabilitiesName)
    {
        $capabilities = $this->object->getCapabilitiesNameForGroup($groupId);
        self::assertEquals($capabilitiesName, $capabilities);
    }

    /**
     * @dataProvider fallBackDevicesIdProvider
     *
     * @param string $deviceId
     * @param string $expected
     */
    public function testGetFallBackDevices($deviceId, $expected)
    {
        $fallBackDevices = $this->object->getFallBackDevices($deviceId);

        self::assertSame($expected, $fallBackDevices);
    }

    /**
     *
     */
    public function fallBackDevicesIdProvider()
    {
        return array(
            array('blackberry_generic_ver2', array('blackberry_generic', 'generic'))
        );
    }

    /**
     *
     */
    public static function groupIdCapabilitiesNameProvider()
    {
        return array(
            array(
                'chtml_ui',
                array(
                    'chtml_display_accesskey',
                    'emoji',
                    'chtml_can_display_images_and_text_on_same_line',
                    'chtml_displays_image_in_center',
                    'imode_region',
                    'chtml_make_phone_call_string',
                    'chtml_table_support'
                )
            )
        );
    }
}
