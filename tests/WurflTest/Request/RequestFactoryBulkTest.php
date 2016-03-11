<?php

namespace WurflTest\Request;

use Wurfl\Request\GenericRequestFactory;
use WurflTest\NotNullCondition;

/**
 * Class RequestFactoryBulkTest
 *
 * @group Request
 */
class RequestFactoryBulkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider loadData
     *
     * @param string $expected
     * @param array  $serverData
     */
    public function testCreateRequest($expected, array $serverData)
    {
        $requestFactory = new GenericRequestFactory();
        $request        = $requestFactory->createRequest($serverData);

        self::assertEquals($expected, $request->getUserAgent());
    }

    /**
     * @return array[]
     */
    public function loadData()
    {
        $configurationFile = 'tests/resources/request.yml';
        $handle            = fopen($configurationFile, 'r');

        if (!$handle) {
            return array();
        }

        $testData         = array();
        $notNullCondition = new NotNullCondition();

        while (!feof($handle)) {
            $line = fgets($handle, 4096);
            if (strpos($line, '#') === false && strcmp($line, '\n') !== 0) {
                $values     = explode(':', trim($line));
                $keys       = array(
                    'HTTP_USER_AGENT',
                    'HTTP_X_DEVICE_USER_AGENT',
                    'HTTP_X_SKYFIRE_VERSION',
                    'HTTP_X_BLUECOAT_VIA',
                    'EXPECTED_USER_AGENT',
                );
                $serverData = $this->arrayCombine($keys, $values, $notNullCondition);
                $testData[] = array(
                    'EXPECTED_USER_AGENT' => $serverData['EXPECTED_USER_AGENT'],
                    '_SERVER'             => $serverData,
                );
            }
        }
        fclose($handle);

        return $testData;
    }

    private function arrayCombine(array $keys, array $values, NotNullCondition $condition = null)
    {
        if (is_null($condition)) {
            return array_combine($keys, $values);
        }
        $count         = count($keys);
        $combinedArray = array();
        for ($i = 0; $i < $count; ++$i) {
            if ($condition->check($keys[$i], $values[$i])) {
                $combinedArray[$keys[$i]] = $values[$i];
            }
        }

        return $combinedArray;
    }
}
