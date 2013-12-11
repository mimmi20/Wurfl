<?php
/**
 * test case
 */
/**
 * test case.
 */
class WURFL_Request_UserAgentNormalizer_BaseTest extends PHPUnit_Framework_TestCase
{
    /** @var  Wurfl\Request\Normalizer\NormalizerInterface */
    protected $normalizer;

    public function assertNormalizeEqualsExpected($userAgent, $expected)
    {
        $actual = $this->normalizer->normalize($userAgent);
        self::assertEquals($expected, $actual, $userAgent);
    }

    protected function userAgentsProvider($testFilePath)
    {

        $fullTestFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $testFilePath;
        $useragents       = file($fullTestFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $map              = array();
        foreach ($useragents as $useragent) {
            $map [] = explode("=", $useragent);
        }

        return $map;
    }
}

