<?php

namespace WurflTest\Device;

/*
 * test case
 */
use Psr\Log\NullLogger;
use Wurfl\Device\DeviceRepositoryBuilder;
use Wurfl\Device\Xml\DevicePatcher;
use Wurfl\Handlers\Chain\UserAgentHandlerChainFactory;
use Wurfl\Storage\Storage;
use Wurfl\WurflConstants;
use WurflCache\Adapter\Memory;

/**
 * \Wurfl\DeviceRepositoryBuilder test case.
 *
 * @group Device
 */
class DeviceRepositoryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const WURFL_FILE = 'tests/resources/wurfl.xml';

    /**
     * @var string
     */
    const PATCH_FILE_ONE = 'tests/resources/patch1.xml';

    /**
     * @var string
     */
    const PATCH_FILE_TWO = 'tests/resources/patch2.xml';

    /** @var  \Wurfl\Device\DeviceRepositoryBuilder */
    private $deviceRepositoryBuilder;

    protected function setUp()
    {
        $logger = new NullLogger();

        $persistenceProvider           = new Storage(new Memory());
        $userAgentHandlerChain         = UserAgentHandlerChainFactory::createFrom(
            $persistenceProvider,
            $persistenceProvider,
            $logger
        );
        $devicePatcher                 = new DevicePatcher();
        $this->deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $persistenceProvider,
            $userAgentHandlerChain,
            $devicePatcher,
            $logger
        );
    }

    public function testShouldBuildARepositoryOfAllDevicesFromTheXmlFile()
    {
        $deviceRepository = $this->deviceRepositoryBuilder->build(self::WURFL_FILE);
        self::assertNotNull($deviceRepository);
        self::assertEquals('2016-04-04 10:50:09 -0400', $deviceRepository->getLastUpdated());
        $genericDevice = $deviceRepository->getDevice('generic');
        self::assertNotNull($genericDevice, 'generic device is null');
    }

    public function testShouldAddNewDevice()
    {
        $deviceRepository = $this->deviceRepositoryBuilder->build(self::WURFL_FILE, array(self::PATCH_FILE_ONE));
        self::assertNotNull($deviceRepository);
        $newDevice1 = $deviceRepository->getDevice('generic_web_browser');
        self::assertNotNull($newDevice1, 'generic web browser device is null');
        self::assertEquals('770', $newDevice1->getCapability('columns'));
    }

    public function testShouldApplyMoreThanOnePatches()
    {
        $deviceRepository = $this->deviceRepositoryBuilder->build(
            self::WURFL_FILE,
            array(self::PATCH_FILE_ONE, self::PATCH_FILE_TWO)
        );
        self::assertNotNull($deviceRepository);
        $newDevice1 = $deviceRepository->getDevice('generic_web_browser');
        self::assertNotNull($newDevice1, 'generic web browser device is null');
        self::assertEquals('770', $newDevice1->getCapability('columns'));

        $newDevice2 = $deviceRepository->getDevice('generic_web_browser_new');
        self::assertNotNull($newDevice2, 'generic web browser device is null');
        self::assertEquals('7', $newDevice2->getCapability('columns'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There is no device with ID [generic] in the loaded WURFL Data
     */
    public function testShouldNotRebuildTheRepositoryIfAlreadyBuild()
    {
        $logger = new NullLogger();

        $persistenceProvider     = new Storage(new Memory());
        $persistenceProvider->setWURFLLoaded(true);
        $userAgentHandlerChain   = UserAgentHandlerChainFactory::createFrom(
            $persistenceProvider,
            $persistenceProvider,
            $logger
        );
        $devicePatcher           = new DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $persistenceProvider,
            $userAgentHandlerChain,
            $devicePatcher,
            $logger
        );
        self::assertNotNull($deviceRepositoryBuilder);

        $deviceRepository = $deviceRepositoryBuilder->build(self::WURFL_FILE);
        $deviceRepository->getDevice(WurflConstants::GENERIC);
    }
}
