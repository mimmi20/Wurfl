<?php
/**
 * test case
 */
/**
 * test case.
 */
class WURFL_WURFLReloadingTest extends PHPUnit_Framework_TestCase
{

    private $wurflManager;
    private $wurflManagerFactory;

    const WURFL_CONFIG_FILE = "../resources/wurfl-config.xml";

    public function setUp()
    {
        $configurationFile         = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_CONFIG_FILE;
        $config                    = new \Wurfl\Configuration\XmlConfig($configurationFile);
        $this->wurflManagerFactory = new \Wurfl\ManagerFactory ($config);
        $this->wurflManager        = $this->wurflManagerFactory->create();
    }

    public function tearDown()
    {
        if (!$this->wurflManagerFactory instanceof \Wurfl\ManagerFactory) {
            return;
        }
        $this->wurflManagerFactory->remove();
    }

    public function testShouldReloadWURFLIfWURFLFileTimeStampChanges()
    {
        $configurationFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::WURFL_CONFIG_FILE;
        $wurflConfig       = \Wurfl\Configuration\ConfigFactory::create($configurationFile);
        touch($wurflConfig->wurflFile);
    }
}

