<?php

namespace WurflTest\Handlers;

use UaNormalizer\Helper\Utils;

/**
 * Class ToleranceTest
 *
 * @group Handlers
 */
class ToleranceTest extends \PHPUnit_Framework_TestCase
{
    public function testFirstSlash()
    {
        $this->assertEquals(6, Utils::firstSlash('Value/12'));
        $this->assertNull(Utils::firstSlash('Value'));
    }

    public function testSecondSlash()
    {
        $this->assertEquals(9, Utils::secondSlash('Value/12/13'));
        $this->assertNull(Utils::secondSlash('Value/12'));
        $this->assertNull(Utils::secondSlash('Value'));
    }

    public function testFirstSpace()
    {
        $this->assertEquals(6, Utils::firstSpace('Value 12'));
        $this->assertNull(Utils::firstSpace('Value'));
    }

    public function testOpenParen()
    {
        $this->assertEquals(6, Utils::firstOpenParen('Value(12)'));
        $this->assertNull(Utils::firstOpenParen('Value'));
    }

    public function testCloseParen()
    {
        $this->assertEquals(9, Utils::firstCloseParen('Value(12)'));
        $this->assertNull(Utils::firstCloseParen('Value'));
    }
}
