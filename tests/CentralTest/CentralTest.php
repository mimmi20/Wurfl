<?php
class CentralTest {
	
	const MODE_HIGH_PERFORMANCE = 'high_performance';
	const MODE_HIGH_ACCURACY = 'high_accuracy';
	const MODE_ALL = 'all';
	
	public $test_id;
	public $name;
	public $description;
	public $type_unit;
	public $type_regression;
	public $mode;
	public $min_version;
	public $ua_override;
	public $enabled;
	public $http_method = 'get';
	public $http_headers = array(
		'HTTP_USER_AGENT' => '',
		'HTTP_ACCEPT' => '',
	);
	public $assertions = array();
	public $failure_count = 0;
	
	public function run(WURFL_WURFLManager $wurflManager) {
		$this->failure_count = 0;

		// Verify API Version
		if ($this->min_version && version_compare(WURFL_Constants::API_VERSION, $this->min_version, '<')) {
			throw new CentralTest_InvalidIntrospectorVersionException("Test requires WURFL PHP API >= $this->min_version (current $wurflManager->release_version)");
		}

		// Verify correct mode
		if ($this->mode != self::MODE_ALL) {
			$required_mode = ($this->mode == self::MODE_HIGH_PERFORMANCE)? 
					WURFL_Configuration_Config::MATCH_MODE_PERFORMANCE: 
					WURFL_Configuration_Config::MATCH_MODE_ACCURACY;

			WURFL_Configuration_ConfigHolder::getWURFLConfig()->matchMode($required_mode);
		}

        $request_factory = new WURFL_Request_GenericRequestFactory();
        $request = $request_factory->createRequest($this->http_headers, $this->ua_override);
		$device = $wurflManager->getDeviceForRequest($request);

		foreach ($this->assertions as $assertion) {
			/* @var $assertion CentralTestAssertion */
            $assertion->wurflManager = $wurflManager;
			if (!$assertion->assert($device)) {
				$this->failure_count++;
			}
		}
		return ($this->failure_count === 0);
	}

	/**
	 * @return array Failed assertions
	 */
	public function getFailedAssertions() {
		$out = array();
		foreach ($this->assertions as $assertion) {
			/* @var $assertion CentralTestAssertion */
			if ($assertion->result === false) {
				$out[] = $assertion;
			}
		}
		return $out;
	}
}

class CentralTest_InvalidIntrospectorModeException extends Exception {};
class CentralTest_InvalidIntrospectorVersionException extends Exception {};
