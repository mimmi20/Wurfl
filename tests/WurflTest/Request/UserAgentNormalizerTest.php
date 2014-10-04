<?php
namespace WurflTest\Request;

use Wurfl\Request\Normalizer\Generic\BabelFish;
use Wurfl\Request\Normalizer\Specific\Chrome;
use Wurfl\Request\Normalizer\UserAgentNormalizer;

/**
 * test case
 */
class UserAgentNormalizerTest
    extends \PHPUnit_Framework_TestCase
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
