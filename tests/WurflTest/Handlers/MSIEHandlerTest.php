<?php

namespace WurflTest\Handlers;

use Wurfl\Handlers\MSIEHandler;
use UaNormalizer\Specific\MSIE;

/**
 * Class MSIEHandlerTest
 *
 * @group Handlers
 */
class MSIEHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MSIEHandler */
    private $msieHandler;

    protected function setUp()
    {
        $userAgentNormalizer = new MSIE();
        $this->msieHandler   = new MSIEHandler($userAgentNormalizer);
    }

    public function testShoudHandle()
    {
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        self::assertTrue($this->msieHandler->canHandle($userAgent));
    }
}
