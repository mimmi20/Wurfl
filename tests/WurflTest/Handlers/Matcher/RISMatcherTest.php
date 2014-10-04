<?php
namespace WurflTest\Handlers\Matcher;

/**
 * test case
 */
use Wurfl\Handlers\Matcher\RISMatcher;

/**
 * WURFL_Handlers_Matcher_RISMatcher test case.
 */
class RISMatcherTest
    extends \PHPUnit_Framework_TestCase
{
    /** @var  RISMatcher */
    private $risMatcher;

    protected function setUp()
    {
        $this->risMatcher = RISMatcher::getInstance();
    }

    /**
     * @dataProvider risData
     */
    public function testMatch($candidates, $needle, $tolerance, $expected)
    {
        $result = $this->risMatcher->match($candidates, $needle, $tolerance);
        self::assertEquals($expected, $result);
    }

    public function testMatchMustReturnFirstMatch()
    {

        $expected = "aaa bbb 1";
        $needle   = "aaa bbb 4";

        $candidates = array("aaa bbb 1", "aaa bbb 2", "aaa bbb 3", "aaa bbb 5", "aaa bbb 6");

        $match = $this->risMatcher->match($candidates, $needle, 1);

        self::assertEquals($expected, $match);
    }

    public function risData()
    {

        $candidates = array("aaa bbb ccc ddd", "aaa bbb ccc", "aaa bbb", "aaa", "aaa xxx");
        sort($candidates);

        return array(
            array($candidates, "aaa bbb ccc ddd", 15, "aaa bbb ccc ddd"),
            array($candidates, "aaa bbb ccc xxx", 15, null), //
            array($candidates, "aaa bbb ccc", 11, "aaa bbb ccc"),
            array($candidates, "aaa bbb ccc ddd", 3, "aaa bbb ccc ddd")
        );
    }
}
