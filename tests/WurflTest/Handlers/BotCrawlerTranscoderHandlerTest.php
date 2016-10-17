<?php

namespace WurflTest\Handlers;

use Wurfl\Handlers\BotCrawlerTranscoderHandler;
use UaNormalizer\NullNormalizer;

/**
 * Class BotCrawlerTranscoderHandlerTest
 *
 * @group Handlers
 */
class BotCrawlerTranscoderHandlerTest extends \PHPUnit_Framework_TestCase
{
    const BOT_CRAWLER_TRANSCODER_FILE_PATH = 'bot_crawler_transcoder.txt';

    /** @var  BotCrawlerTranscoderHandler */
    private $handler;

    protected function setUp()
    {
        $normalizer    = new NullNormalizer();
        $this->handler = new BotCrawlerTranscoderHandler($normalizer);
    }

    /**
     * @dataProvider botCrawlerTranscoderUserAgentsProvider
     *
     * @param string $userAgent
     */
    public function testShoudHandleKnownBots($userAgent)
    {
        $found = $this->handler->canHandle($userAgent);
        self::assertTrue($found);
    }

    public function botCrawlerTranscoderUserAgentsProvider()
    {
        return array(
            array('Mozilla/5.0 (compatible; BecomeBot/3.0; +http://www.become.com/site_owners.html)'),
            array('Mozilla/5.0 (compatible; DBLBot/1.0; +http://www.dontbuylists.com/)'),
        );
    }
}
