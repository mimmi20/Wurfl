<?php

namespace WurflTest\Request;

use UaNormalizer\Generic\BabelFish;
use UaNormalizer\Specific\Chrome;
use UaNormalizer\UserAgentNormalizer;

/**
 * Class UserAgentNormalizerTest
 *
 * @group Request
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
