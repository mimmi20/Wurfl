<?php
namespace WurflTest\Request;

use Wurfl\Request\GenericRequest;
use Wurfl\Request\GenericRequestFactory;

/**
 * test case
 */
class GenericRequestFactoryTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Wurfl\Request\GenericRequestFactory
     */
    private $object = null;

    public function setUp()
    {
        $this->object = new GenericRequestFactory();
    }

    public function testCreateRequest()
    {
        $userAgent = 'testUA';
        $header    = array(
            'HTTP_USER_AGENT' => $userAgent
        );
        $expected  = new GenericRequest($header, $userAgent, null, false);

        $result = $this->object->createRequest($header, false);

        self::assertInstanceOf('\Wurfl\Request\GenericRequest', $result);
        self::assertEquals($expected, $result);
    }

    public function testCreateRequestForUserAgent()
    {
        $userAgent = 'testUA';
        $header    = array(
            'HTTP_USER_AGENT' => $userAgent
        );
        $expected  = new GenericRequest($header, $userAgent, null, false);

        $result = $this->object->createRequestForUserAgent($userAgent);

        self::assertInstanceOf('\Wurfl\Request\GenericRequest', $result);
        self::assertEquals($expected, $result);
    }
}
