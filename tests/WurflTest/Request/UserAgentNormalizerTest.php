<?php

namespace WurflTest\Request;

use Wurfl\Handlers\Normalizer\Generic\BabelFish;
use Wurfl\Handlers\Normalizer\Specific\Chrome;
use Wurfl\Handlers\Normalizer\UserAgentNormalizer;

/**
 * test case
 */
class UserAgentNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAddANormalizer()
    {
        $userAgentNormalizer = new UserAgentNormalizer();
        $userAgentNormalizer->add(
            new Chrome()
        );

        self::assertEquals(1, $userAgentNormalizer->count());
    }

    public function testShouldAddToAlreadyPresentNormalizers()
    {
        $userAgentNormalizer = new UserAgentNormalizer(
            array(new BabelFish())
        );
        $userAgentNormalizer = $userAgentNormalizer->add(
            new Chrome()
        );

        self::assertEquals(2, $userAgentNormalizer->count());
    }
}
