<?php
/**
 * test case
 */

/**
 * WURFL_Handlers_Utils test case.
 */
class WURFL_Handlers_UtilsTest extends PHPUnit_Framework_TestCase {
    
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldThrowExceptionForNullString() {
        \Wurfl\Handlers\Utils::ordinalIndexOf ( NULL, "", 0 );
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldThrowExceptionForEmptyString() {
        \Wurfl\Handlers\Utils::ordinalIndexOf ( "", "", 0 );
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testShouldThrowExceptionForNonNumericOrdinalVlaue() {
        \Wurfl\Handlers\Utils::ordinalIndexOf ( "useranget", "", "" );
    }
    
    /**
     * @dataProvider ordinalIndexOfDataProvider
     */
    public function testOrdinalIndexOf($haystack, $needle, $ordinal, $expectedIndex) {
        $found = \Wurfl\Handlers\Utils::ordinalIndexOf ( $haystack, $needle, $ordinal );
        $this->assertEquals ( $expectedIndex, $found );
    
    }
    
    public function testShouldReturnNegativeOneForInexistantChar() {
        $haystack = "Mozilla/4.0 (compatible; MSIE 4.0; Windows 95; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        $needle = ":";
        $expected = \Wurfl\Handlers\Utils::ordinalIndexOf ( $haystack, $needle, 1 );
        $this->assertEquals ( - 1, $expected );
    
    }

    /**
     * @dataProvider containsAllDataProvider
     */    
    public function testContainsAll($haystack, $needles, $contains) {
        $expected = \Wurfl\Handlers\Utils::checkIfContainsAll($haystack, $needles );
        $this->assertEquals ($contains, $expected );                
    }

    public static function containsAllDataProvider() {
        return array (
            array("aab aac aad", array("aab", "aad"), true),            
            array("aab / ", array("aab", "aac"), false),
            array("abcdef ", array("ab", "ef"), true),
        );
    }

    /**
     * @dataProvider indexOfAnyOrLengthDataProvider
     */    
    public function testIndexOfAnyOrLength($haystack, $expected) {
        $found = \Wurfl\Handlers\Utils::indexOfAnyOrLength($haystack, array(" ", "/"), 0);
        $this->assertEquals($expected, $found);
    }
    
    
    public static function indexOfAnyOrLengthDataProvider() {
        return array (
            array("aab/ ", 3),
            array("aab / ", 3),
            array("aab", 3),
        );
    }
    
    
    public static function ordinalIndexOfOrLengthDataProvider() {
        return array (
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 6.9) VZW:SCH-i760 PPC 240x320", "/", 1, 7 ), 
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 1, 23 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 2, 33 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 3, 49 ), 
            array ("Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1)", "/", 1, 7 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; GoodAccess 3.7.0.9 (PalmOS 5.1))", ";", 4, -1) 
        );
    }
    
    
    public static function ordinalIndexOfDataProvider() {
        return array (
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 6.9) VZW:SCH-i760 PPC 240x320", "/", 1, 7 ), 
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 1, 23 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 2, 33 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", ";", 3, 49 ), 
            array ("Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1)", "/", 1, 7 ),
            array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; GoodAccess 3.7.0.9 (PalmOS 5.1))", ";", 4, -1) 
        );
    }
    
    public static function userAgentsWithThirdSemiColumn() {
        return array (array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 6.9) VZW:SCH-i760 PPC 240x320", 38 ), array ("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; InfoPath.1; .NET CLR 1.1.4322)", 42 ), array ("Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1)", 42 ) );
    }
    
}

