<?php
/**
 * test case
 */

class WURFL_Xml_DeviceIteratorTest extends PHPUnit_Framework_TestCase {
    
    const RESOURCES_DIR = "../../resources/";
    const WURFL_FILE = "../../resources/wurfl_base.xml";
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldLaunchExceptionForInvalidInputFile() {
        $wurflFile = "";
        new \Wurfl\Xml\DeviceIterator ( $wurflFile );
    
    }
    
    public function testShouldReadTheSpecificAttribute() {
        $wurflFile = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . self::RESOURCES_DIR . "wurfl-specific-attribute.xml";
        
        $deviceIterator = new \Wurfl\Xml\DeviceIterator ( $wurflFile );
        $devices = $this->toList($deviceIterator);
        
        $this->assertEquals("foo", $devices[0]->id);
        $this->assertTrue($devices[0]->specific);
        
        $this->assertFalse($devices[1]->specific);
    }
    
    private function toList($deviceIterator) {
        $deviceList = array();
        foreach ($deviceIterator as $device) {
            $deviceList[] = $device;
        }
        return $deviceList;
    }
    
    public function testShouldLoadAllCapabilties() {
        $wurflFile = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . self::WURFL_FILE;
        
        $deviceIterator = new \Wurfl\Xml\DeviceIterator ( $wurflFile );
        foreach ( $deviceIterator as $device ) {
            $capsByGroupsId = $device->getGroupIdCapabilitiesMap ();
            $this->assertTrue ( count ( $capsByGroupsId ) > 2 );
        }
    }
    
    private function process($device) {
        $this->assertNotNull ( $device );
    }

}

