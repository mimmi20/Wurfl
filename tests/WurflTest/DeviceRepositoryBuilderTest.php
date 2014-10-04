<?php
namespace WurflTest;

/**
 * test case
 */
use Wurfl\DeviceRepositoryBuilder;
use Wurfl\Storage\Storage;
use Wurfl\UserAgentHandlerChainFactory;
use Wurfl\Xml\DevicePatcher;
use WurflCache\Adapter\Memory;

/**
 * \Wurfl\DeviceRepositoryBuilder test case.
 */
class DeviceRepositoryBuilderTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const WURFL_FILE = '../resources/wurfl_base.xml';

    /**
     * @var string
     */
    const PATCH_FILE_ONE = '../resources/patch1.xml';

    /**
     * @var string
     */
    const PATCH_FILE_TWO = '../resources/patch2.xml';

    /** @var  DeviceRepositoryBuilder */
    private $deviceRepositoryBuilder;

    protected function setUp()
    {
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

    public function testShouldBuildARepositoryOfAllDevicesFromTheXmlFile()
    {
        $wurflFile        = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_FILE;
        $deviceRepository = $this->deviceRepositoryBuilder->build($wurflFile);
        self::assertNotNull($deviceRepository);
        self::assertEquals("Thu Jun 03 12:01:14 -0500 2010", $deviceRepository->getLastUpdated());
        $genericDevice = $deviceRepository->getDevice("generic");
        self::assertNotNull($genericDevice, "generic device is null");
    }

    public function testShouldAddNewDevice()
    {
        $wurflFile  = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_FILE;
        $patchFile1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::PATCH_FILE_ONE;

        $deviceRepository = $this->deviceRepositoryBuilder->build($wurflFile, array($patchFile1));
        self::assertNotNull($deviceRepository);
        $newDevice1 = $deviceRepository->getDevice("generic_web_browser");
        self::assertNotNull($newDevice1, "generic web browser device is null");
        self::assertEquals("770", $newDevice1->getCapability("columns"));
    }

    public function testShouldApplyMoreThanOnePatches()
    {
        $wurflFile  = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_FILE;
        $patchFile1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::PATCH_FILE_ONE;
        $patchFile2 = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::PATCH_FILE_TWO;

        $deviceRepository = $this->deviceRepositoryBuilder->build($wurflFile, array($patchFile1, $patchFile2));
        self::assertNotNull($deviceRepository);
        $newDevice1 = $deviceRepository->getDevice("generic_web_browser");
        self::assertNotNull($newDevice1, "generic web browser device is null");
        self::assertEquals("770", $newDevice1->getCapability("columns"));

        $newDevice2 = $deviceRepository->getDevice("generic_web_browser_new");
        self::assertNotNull($newDevice2, "generic web browser device is null");
        self::assertEquals("7", $newDevice2->getCapability("columns"));
    }

    public function testShouldNotRebuildTheRepositoryIfAlreadyBuild()
    {
        $persistenceProvider = new Storage(new Memory());
        $persistenceProvider->setWURFLLoaded(true);
        $userAgentHandlerChain   = UserAgentHandlerChainFactory::createFrom($persistenceProvider, $persistenceProvider);
        $devicePatcher           = new DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $persistenceProvider, $userAgentHandlerChain, $devicePatcher
        );
        self::assertNotNull($deviceRepositoryBuilder);
        $wurflFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_FILE;

        try {
            $deviceRepository = $deviceRepositoryBuilder->build($wurflFile);
            $deviceRepository->getDevice("generic");
        } catch (\Exception $ex) {
        }
    }
}
