<?php
namespace WurflTest\Handlers;

    /**
 * test case
 */
use Wurfl\Context;
use Wurfl\Handlers\MotorolaHandler;
use Wurfl\Request\Normalizer\NullNormalizer;

/**
 *
 */
class MotorolaHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MotorolaHandler */
    private $motorolaHandler;

    function setUp()
    {
        $context               = new Context (null);
        $userAgentNormalizer   = new NullNormalizer ();
        $this->motorolaHandler = new MotorolaHandler ($context, $userAgentNormalizer);
    }

    public function testShouldNotHandle()
    {
        $userAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        self::assertFalse($this->motorolaHandler->canHandle($userAgent));
    }

    public function testShouldHandle()
    {
        $userAgent = "MOT-Z6w/R6635_G_81.01.61R Profile/MIDP-2.0 Configuration/CLDC-1.1 Symphony 1.0";
        self::assertTrue($this->motorolaHandler->canHandle($userAgent));
    }
}
