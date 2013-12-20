<?php
namespace WurflTest\Handlers;

use Wurfl\Handlers\BotCrawlerTranscoderHandler;

/**
 * test case
 */
/**
 *  test case.
 */
class BotCrawlerTranscoderHandlerTest extends \PHPUnit_Framework_TestCase
{

    const BOT_CRAWLER_TRANSCODER_FILE_PATH = "bot_crawler_transcoder.txt";

    /** @var  BotCrawlerTranscoderHandler */
    private $handler;

    function setUp()
    {
        $normalizer    = new \Wurfl\Request\Normalizer\NullNormalizer();
        $context       = new \Wurfl\Context(null);
        $this->handler = new BotCrawlerTranscoderHandler($context, $normalizer);
    }

    /**
     * @dataProvider botCrawlerTranscoderUserAgentsProvider
     *
     * @param string $userAgent
     */
    function testShoudHandleKnownBots($userAgent)
    {
        $found = $this->handler->canHandle($userAgent);
        self::assertTrue($found);
    }

    function botCrawlerTranscoderUserAgentsProvider()
    {
        return array(
            array("Mozilla/5.0 (compatible; BecomeBot/3.0; +http://www.become.com/site_owners.html)"),
            array("Mozilla/5.0 (compatible; DBLBot/1.0; +http://www.dontbuylists.com/)")
        );
    }
}

