<?php
namespace WurflTest;

use Wurfl\Request\GenericRequestFactory;

/**
 * test case
 */
class WURFLRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $_testData = array();

    public function setUp()
    {
        $configurationFile = __DIR__ . DIRECTORY_SEPARATOR . '../resources/request.yml';
        $this->_testData   = self::_loadData($configurationFile);
    }

    public function testCreateRequest()
    {
        foreach ($this->_testData as $testData) {
            $requestFactory = new GenericRequestFactory();
            $request = $requestFactory->createRequest($testData ['_SERVER']);

            self::assertEquals($request->userAgent, $testData ['EXPECTED_USER_AGENT']);
        }
    }

    private static function _loadData($fileName)
    {
        $handle           = fopen($fileName, 'r');
        $testData         = array();
        $notNullCondition = new NotNullCondition();
        if ($handle) {
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if (strpos($line, '#') === false && strcmp($line, "\n") != 0) {
                    $values     = explode(':', trim($line));
                    $keys       = array(
                        'HTTP_USER_AGENT',
                        'HTTP_X_DEVICE_USER_AGENT',
                        'HTTP_X_SKYFIRE_VERSION',
                        'HTTP_X_BLUECOAT_VIA',
                        'EXPECTED_USER_AGENT'
                    );
                    $serverData = self::arrayCombine($keys, $values, $notNullCondition);
                    $testData[] = array(
                        '_SERVER'             => $serverData,
                        'EXPECTED_USER_AGENT' => $serverData['EXPECTED_USER_AGENT']
                    );
                }
            }
            fclose($handle);
        }

        return $testData;
    }

    private static function arrayCombine(array $keys, array $values, NotNullCondition $condition = null)
    {
        if (is_null($condition)) {
            return array_combine($keys, $values);
        }
        $count         = count($keys);
        $combinedArray = array();
        for ($i = 0; $i < $count; $i++) {
            if ($condition->check($keys[$i], $values[$i])) {
                $combinedArray[$keys[$i]] = $values[$i];
            }
        }

        return $combinedArray;
    }
}

/**
 * Utility Classes
 *
 */
//interface Condition {
//    function check($key, $value);
//}

class NotNullCondition
{
    public function check($key, $value)
    {
        return empty($key) || empty($value) ? false : true;
    }
}

