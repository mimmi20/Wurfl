<?php
require_once dirname(__FILE__) . '/CentralTest.php';
require_once dirname(__FILE__) . '/CentralTestAssertion.php';

class CentralTestManager {
	
	const DOWNLOAD_URL = 'http://www.scientiamobile.com/centralTest/download/';
	const TYPE_REGRESSION = 'regression';
	const TYPE_UNIT = 'unit';
	const TYPE_ALL = 'all';
	
	public $show_success = true;
	
	public $num_success = 0;
	public $num_failure = 0;
	
	protected $introspector;

    /**
     * @var $wurflManager WURFL_WURFLManager
     */
	protected $wurflManager;
	protected $test_list = array();
	
	public function __construct(WURFL_WURFLManager $wurflManager) {
		$this->wurflManager = $wurflManager;
	}
	
	public function useIntrospector($remote_url, $username=null, $password=null) {
		$parsed_url = parse_url($remote_url);
		if (isset($parsed_url['user']) && isset($parsed_url['pass'])) {
			// Extract username and password from URL
			$username = $parsed_url['user'];
			$password = $parsed_url['pass'];
			unset($parsed_url['user']);
			unset($parsed_url['pass']);
			$remote_url = self::compose_url($parsed_url);
		}
		if (!class_exists('IntrospectorClient', false)) require_once dirname(__FILE__).'/IntrospectorClient.php';
		$this->introspector = new IntrospectorClient($remote_url, $username, $password);
	}
	
	public function runBatchTest ($test_type) {
		if (!in_array($test_type, array(self::TYPE_REGRESSION, self::TYPE_UNIT, self::TYPE_ALL))) {
			throw new InvalidArgumentException("Invalid test type specified");
		}
		$this->loadTestList(self::DOWNLOAD_URL.$test_type);
		$this->run();
	}
	
	public function runSingleTest ($test_name) {
		$this->loadTestList(self::DOWNLOAD_URL.$test_name);
		$this->run();
	}
	
	public function run() {
		$time_start = time();
		foreach ($this->test_list as $test) {
			/* @var $test CentralTest */
			try {
				$result = $this->introspector? $test->runFromIntrospector($this->wurflManager, $this->introspector): $test->run($this->wurflManager);

                if ($result) {
					$this->num_success++;
					if ($this->show_success === true) {
						echo $test->name.": succeeded\n";
					}
				} else {
					echo $test->test_id.": ";
					$this->num_failure++;
					$failed_assertions = $test->getFailedAssertions();
					if (count($failed_assertions) == 0) {
						echo "failed (no details)\n";
					} else if (count($failed_assertions) == 1) {
						echo "failed: ".$failed_assertions[0]->details."\n";
					} else {
						echo "failed multiple assertions:\n";
						foreach ($failed_assertions as $assertion) {
							echo "\t".$assertion->details."\n";
						}
					}
					echo "[".$test->http_headers['HTTP_USER_AGENT']."]\n\n";
				}
			} catch (CentralTest_InvalidIntrospectorModeException $e) {
				echo "Test $test->test_id skipped: ".$e->getMessage()."\n";
			} catch (CentralTest_InvalidIntrospectorVersionException $e) {
				echo "Test $test->test_id skipped: ".$e->getMessage()."\n";
			} catch (Exception $e) {
				echo get_class($e)." thrown in test $test->test_id: ".$e->getMessage()."\n";
				echo "[".$test->http_headers['HTTP_USER_AGENT']."]\n\n";
				$this->num_failure++;
			}
		}
		$total_tests = count($this->test_list);
		$total_time = time() - $time_start;
		echo "\nTesting completed in $total_time seconds\nTotal Tests: $total_tests\nSuccess: {$this->num_success}\nFailures:  {$this->num_failure}\n";
	}
	
	public function loadTestList ($url) {
		$this->test_list = array();
		$xmlstring = file_get_contents($url);
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xmlstring);
		if (!$xml) {
			$errors = array();
			$i = 1;
			foreach(libxml_get_errors() as $error) {
				$errors[] = "Error #$i: ".$error->message.";";
				$i++;
			}
			throw new Exception("Unable to process XML data:\n".implode("\n",$errors));
		}
		foreach ($xml->test as $testxml) {
			$test = new CentralTest();
			$test->test_id = (string)$testxml['id'];
			$test->name = (string)$testxml['name'];
			$test->mode = (string)$testxml['mode'];
			$test->min_version = (string)$testxml['min_version'];
			$test->ua_override = (string)$testxml['ua_override'];
			$test->description = (string)$testxml['description'];
			$test->type_unit = (string)$testxml['type_unit'];
			$test->type_regression = (string)$testxml['type_regression'];

			// Cleanup variables
			$test->min_version = preg_match('/^\d/', $test->min_version)? $test->min_version: null;
			$test->ua_override = ($test->ua_override != "false");

			// Add HTTP Headers
			foreach ($testxml->http_request as $requestxml) {
				foreach ($requestxml->header as $headerxml) {
					// Change headers to PHP format (User-Agent => HTTP_USER_AGENT)
					$key = 'HTTP_'.strtoupper(str_replace('-', '_', (string)$headerxml['name']));
					$test->http_headers[$key] = empty($headerxml['value'])? (string)$headerxml: (string)$headerxml['value'];
				}
			}
			// Add Device Assertions
			foreach ($testxml->assertions->device as $el) {
				$assertion = new CentralTestAssertion();
				$assertion->assertion_type = CentralTestAssertion::TYPE_DEVICE;
				$assertion->expected_id = (string)$el['id'];
				$assertion->exact_match = ((string)$el['exact'] == 'true');
				$test->assertions[] = $assertion;
			}
			// Add Capability Assertions
			foreach ($testxml->assertions->capability as $el) {
				$assertion = new CentralTestAssertion();
				$assertion->assertion_type = CentralTestAssertion::TYPE_CAPABILITY;
				$assertion->expected_id = (string)$el['name'];
				$assertion->expected_value = (string)$el['value'];
				$test->assertions[] = $assertion;
			}
			// Add Capability Assertions
			foreach ($testxml->assertions->virtual_capability as $el) {
				$assertion = new CentralTestAssertion();
				$assertion->assertion_type = CentralTestAssertion::TYPE_VIRTUAL_CAPABILITY;
				$assertion->expected_id = (string)$el['name'];
				$assertion->expected_value = (string)$el['value'];
				$test->assertions[] = $assertion;
			}
			// Add this test to the Test List
			$this->test_list[] = $test;
		}
	}
	public static function compose_url($parsed_url) {
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		return "$scheme$user$pass$host$port$path$query$fragment";
	}
}