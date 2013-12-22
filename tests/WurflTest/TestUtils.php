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
    static function loadUserAgentsWithIdFromFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File path $filePath does not exist!!!");
        }

        $testData    = array();
        $file_handle = fopen($filePath, "r");

        while (!feof($file_handle)) {
            $line = fgets($file_handle);
            self::updateTestData($testData, $line);
        }
        fclose($file_handle);

        return $testData;
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    static function loadUserAgentsAsArray($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File path $filePath does not exist!!!");
        }

        $testData    = array();
        $fileHandle = fopen($filePath, "r");

        while (!feof($fileHandle)) {
            $line       = fgets($fileHandle);
            $isTestData = ((strpos($line, "#") === false) && strcmp($line, "\n") != 0);

            if ($isTestData) {
                $userAgentArray   = array();
                $userAgentArray[] = $line;
                $testData[]       = $userAgentArray;
            }
        }
        fclose($fileHandle);

        return $testData;
    }

    static function loadTestData($fileName)
    {
        $testData    = array();
        $fileHandle = fopen($fileName, "r");

        while (!feof($fileHandle)) {
            $line = fgets($fileHandle);

            if (strpos($line, "#") === false && strcmp($line, "\n") != 0) {
                $testData[] = explode("=", trim($line));
            }
        }

        fclose($fileHandle);

        return $testData;
    }

    static private function updateTestData(&$testData, $line)
    {
        $isTestData = ((strpos($line, "#") === false) && strcmp($line, "\n") != 0);

        if ($isTestData) {
            $testData[] = explode("=", trim($line));
        }
    }
}

