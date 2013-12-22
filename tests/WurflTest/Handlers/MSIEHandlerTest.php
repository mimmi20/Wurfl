<?php
namespace WurflTest\Handlers;

use Wurfl\Context;
use Wurfl\Handlers\MSIEHandler;
use Wurfl\Request\Normalizer\Specific\MSIE;

/**
 * test case
 */
/**
 * test case.
 */
class MSIEHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MSIEHandler */
    private $msieHandler;

    function setUp()
    {
        $context             = new Context (null);
        $userAgentNormalizer = new MSIE ();
        $this->msieHandler   = new MSIEHandler ($context, $userAgentNormalizer);
    }

    function testShoudHandle()
    {
        $userAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        self::assertTrue($this->msieHandler->canHandle($userAgent));
    }
}

