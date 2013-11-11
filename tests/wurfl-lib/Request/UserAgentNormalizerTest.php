<?php
/**
 * test case
 */
class WURFL_Request_UserAgentNormalizerTest extends PHPUnit_Framework_TestCase
{

    function testShouldAddANormalizer()
    {
        $userAgentNormalizer = new \Wurfl\Request\UserAgentNormalizer();
        $currentNormalizer   = $userAgentNormalizer->addUserAgentNormalizer(
            new \Wurfl\Request\UserAgentNormalizer\Specific\Chrome()
        );

        $this->assertEquals(0, $userAgentNormalizer->count());
        $this->assertEquals(1, $currentNormalizer->count());
    }

    function testShouldAddToAlreadyPresentNormalizers()
    {
        $userAgentNormalizer = new \Wurfl\Request\UserAgentNormalizer(array(new \Wurfl\Request\UserAgentNormalizer\Generic\BabelFish()));
        $userAgentNormalizer = $userAgentNormalizer->addUserAgentNormalizer(
            new \Wurfl\Request\UserAgentNormalizer\Specific\Chrome()
        );

        $this->assertEquals(2, $userAgentNormalizer->count());
    }
}