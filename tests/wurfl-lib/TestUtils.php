<?php
/**
 * test case
 */
class WURFL_TestUtils {
	
	/**
	 * Load Test File containing user-agent -> deviceids associations
	 *
	 * @param string $fileName
	 * @return array
	 */
	static function loadUserAgentsWithIdFromFile($filePath) {
		if(!file_exists($filePath)) {
			throw new InvalidArgumentException("File path $filePath does not exist!!!");
		}
				
		$testData = array ();
		$file_handle = fopen ( $filePath, "r" );
		
		while ( ! feof ( $file_handle ) ) {
			$line = fgets ( $file_handle );
			self::updateTestData($testData, $line);
		}
		fclose ( $file_handle );
		
		return $testData;
		
	}
	
	
	static function loadUserAgentsAsArray($filePath) {
		if(!file_exists($filePath)) {
			throw new InvalidArgumentException("File path $filePath does not exist!!!");
		}
		
		$testData = array ();
		$file_handle = fopen ( $filePath, "r" );
		
		while ( ! feof ( $file_handle ) ) {
			$line = fgets ( $file_handle );
			$isTestData = ((strpos ( $line, "#" ) === false) && strcmp ( $line, "\n" ) != 0);
			if($isTestData) {
				$userAgentArray = array();
				$userAgentArray[] = $line;
				$testData[] = $userAgentArray;
			}
		}
		fclose ( $file_handle );
		
		return $testData;
	}
	
		
	
	static function loadTestData($fileName) {
		$testData = array();
		$file_handle = fopen($fileName, "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
			if(strpos($line, "#") === false && strcmp($line, "\n") != 0) {
				$testData[] = explode("=", trim($line));
			}
		}
		fclose($file_handle);

		return $testData;
	}
	
	
	static private function updateTestData(&$testData, $line) {
		$isTestData = ((strpos ( $line, "#" ) === false) && strcmp ( $line, "\n" ) != 0);
		if ($isTestData) {
			$testData[] = explode("=", trim($line));
		}
	}
	
	

}

