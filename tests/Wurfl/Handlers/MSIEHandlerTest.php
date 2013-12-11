<?php
use Wurfl\Handlers\MSIEHandler;

/**
 * test case
 */
/**
 * test case.
 */
class WURFL_Handlers_MSIEHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var  MSIEHandler */
    private $msieHandler;

    function setUp()
    {
        $context             = new \Wurfl\Context (null);
        $userAgentNormalizer = new \Wurfl\Request\Normalizer\Specific\MSIE ();
        $this->msieHandler   = new MSIEHandler ($context, $userAgentNormalizer);
    }

    function testShoudHandle()
    {
        $userAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        self::assertTrue($this->msieHandler->canHandle($userAgent));
    }
}

