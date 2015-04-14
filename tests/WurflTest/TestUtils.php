<?php
namespace WurflTest;

class TestUtils
{

    /**
     * Load Test File containing user-agent -> deviceids associations
     *
     * @param $filePath
     *
     * @throws \InvalidArgumentException
     * @internal param string $fileName
     * @return array
     */
    public static function loadUserAgentsWithIdFromFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File path $filePath does not exist!!!');
        }

        $testData   = array();
        $fileHandle = fopen($filePath, 'r');

        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);
            self::updateTestData($testData, $line);
        }
        fclose($fileHandle);

        return $testData;
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function loadUserAgentsAsArray($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File path $filePath does not exist!!!');
        }

        $testData   = array();
        $fileHandle = fopen($filePath, 'r');

        while (!feof($fileHandle)) {
            $line       = fgets($fileHandle);
            $isTestData = ((strpos($line, '#') === false) && strcmp($line, '\n') != 0);

            if ($isTestData) {
                $userAgentArray   = array();
                $userAgentArray[] = $line;
                $testData[]       = $userAgentArray;
            }
        }
        fclose($fileHandle);

        return $testData;
    }

    public static function loadTestData($fileName)
    {
        $testData   = array();
        $fileHandle = fopen($fileName, 'r');

        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);

            if (strpos($line, '#') === false && strcmp($line, '\n') != 0) {
                $testData[] = explode('=', trim($line));
            }
        }

        fclose($fileHandle);

        return $testData;
    }

    private static function updateTestData(&$testData, $line)
    {
        $isTestData = ((strpos($line, '#') === false) && strcmp($line, '\n') != 0);

        if ($isTestData) {
            $testData[] = explode('=', trim($line));
        }
    }
}
