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

        self::assertEquals(0, $userAgentNormalizer->count());
        self::assertEquals(1, $currentNormalizer->count());
    }

    function testShouldAddToAlreadyPresentNormalizers()
    {
        $userAgentNormalizer = new \Wurfl\Request\UserAgentNormalizer(array(new \Wurfl\Request\UserAgentNormalizer\Generic\BabelFish()));
        $userAgentNormalizer = $userAgentNormalizer->addUserAgentNormalizer(
            new \Wurfl\Request\UserAgentNormalizer\Specific\Chrome()
        );

        self::assertEquals(2, $userAgentNormalizer->count());
    }
}