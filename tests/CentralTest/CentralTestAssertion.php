<?php
class CentralTestAssertion {
	
	const TYPE_DEVICE = 'device';
	const TYPE_CAPABILITY = 'capability';
	const TYPE_VIRTUAL_CAPABILITY = 'virtual_capability';
	
	const OPERATOR_EQUAL = '=';
	const OPERATOR_EQUAL_LOOSE = '~';
	const OPERATOR_NOT_EQUAL = '!=';
	const OPERATOR_GREATER_THAN = '>';
	const OPERATOR_LESS_THAN = '<';
	const OPERATOR_GREATER_THAN_EQUAL = '>=';
	const OPERATOR_LESS_THAN_EQUAL = '<=';
	const OPERATOR_STARTS_WITH = '^=';
	const OPERATOR_NOT_STARTS_WITH = '!^';
	const OPERATOR_ENDS_WITH = '$=';
	const OPERATOR_CONTAINS = '*=';
	const OPERATOR_NOT_CONTAINS = '!*';
	
	public $assertion_type;
	public $expected_id;
	public $expected_value;
	public $exact_match;
	public $operator = self::OPERATOR_EQUAL;
	
	public $result;
	public $details = '';

    /**
     * @var WURFL_WURFLManager
     */
    public $wurflManager;

	/**
	 * @param WURFL_CustomDevice $device
	 * @param array $response
	 * @return boolean Success
	 * @throws Exception Invalid assertion type
	 */
	public function assert(WURFL_CustomDevice $device, $response=null) {
		switch ($this->assertion_type) {
			case self::TYPE_DEVICE:
				$this->result = $this->assertDevice($device, $response);
				break;
			case self::TYPE_CAPABILITY:
				$this->result = $this->assertCapability($device, $response);
				break;
			case self::TYPE_VIRTUAL_CAPABILITY:
				$this->result = $this->assertVirtualCapability($device, $response);
				break;
			default:
				throw new Exception("Invalid assertion type");
				break;
		}
		return $this->result; 
	}
	
	public function assertDevice(WURFL_CustomDevice $device, $response=null) {
		if ($this->operator != self::OPERATOR_EQUAL && $this->operator != self::OPERATOR_EQUAL_LOOSE) {
			if (self::compare($this->expected_id, $device->id, $this->operator)) {
				$this->details = "Logical match ($this->operator) succeeded";
				return true;
			} else {
				$this->details = "Logical match failed: '$this->expected_id $this->operator {$device->id}'";
				return false;
			}
		}
		if ($this->expected_id == $device->id) {
			$this->details = 'Exact match succeeded';
			return true;
		} else if ($this->exact_match === true) {
			$this->details = "Exact match required; expected:$this->expected_id, got:{$device->id}";
			return false;
		}

		$target_root_device = $device->getActualDeviceRootAncestor();
		
		// No actual device root means no possible match
		if (!$target_root_device) {
			$this->details = "Exact match not required, but detected device ID has no actual_device_root ancestor; expected:$this->expected_id, got:{$device->id}";
			return false;
		}

		// Perform loose comparison

		// Get reference device
		try {
			$reference_device = $this->wurflManager->getDevice($this->expected_id);
			$reference_root_device = $reference_device->getActualDeviceRootAncestor();
		} catch (Exception $e) {
			throw new Exception("The expected WURFL ID ($this->expected_id) is not in the loaded WURFL Data!  Detected as: {$device->id}", null, $e);
		}

		if ($reference_root_device->id != $target_root_device->id) {
			$this->details = "Loose match required; expected:$this->expected_id, got:{$device->id}";
			return false;
		}

		return true;
	}
	
	public function assertCapability(WURFL_CustomDevice $device, $response=null) {
		$actual = $response? $response['capabilities'][$this->expected_id]: $device->getCapability($this->expected_id);
		if (self::compare($this->expected_value, $actual, $this->operator)) {
			$this->details = 'Capability match succeeded.';
			return true;
		} else {
			$actual_nice = strlen(self::asString($actual))? self::asString($actual): '[null]';
			$this->details = "Capability match failed; [{$this->expected_id}] expected:{$this->expected_value} got:$actual_nice ({$device->id})";
			return false;
		}
	}

	public function assertVirtualCapability(WURFL_CustomDevice $device, $response=null) {
		$actual = $response? $response['capabilities'][$this->expected_id]: $device->getVirtualCapability($this->expected_id);
		if (self::compare($this->expected_value, $actual, $this->operator)) {
			$this->details = 'Virtual capability match succeeded.';
			return true;
		} else {
			$actual_nice = strlen(self::asString($actual))? self::asString($actual): '[null]';
			$this->details = "Virtual capability match failed; [{$this->expected_id}] expected:{$this->expected_value} got:$actual_nice ({$device->id})";
			return false;
		}
	}
	
	public static function compare($val1, $val2, $operator='=') {
		$val1_str = self::asString($val1);
		$val2_str = self::asString($val2);
		
		if (strlen($val2_str) === 0) {
			return ($operator == self::OPERATOR_NOT_EQUAL);
		}
		
		switch ($operator) {
			case self::OPERATOR_EQUAL:
				if (is_numeric($val1) && is_numeric($val2)) {
					return ($val1 == $val2);
				}
				return (strcmp($val1_str, $val2_str) === 0);
				break;
			case self::OPERATOR_NOT_EQUAL:
				if (is_numeric($val1) && is_numeric($val2)) {
					return ($val1 != $val2);
				}
				return (strcmp($val1_str, $val2_str) !== 0);
				break;
			case self::OPERATOR_GREATER_THAN:
				return $val2 > $val1;
				break;
			case self::OPERATOR_LESS_THAN:
				return $val2 < $val1;
				break;
			case self::OPERATOR_GREATER_THAN_EQUAL:
				return $val2 >= $val1;
				break;
			case self::OPERATOR_LESS_THAN_EQUAL:
				return $val2 <= $val1;
				break;
			case self::OPERATOR_STARTS_WITH:
				return strpos($val2, $val1) === 0;
				break;
			case self::OPERATOR_NOT_STARTS_WITH:
				return strpos($val2, $val1) !== 0;
				break;
			case self::OPERATOR_ENDS_WITH:
				return (substr_compare($val2, $val1, -strlen($val1), strlen($val1)) === 0);
				break;
			case self::OPERATOR_CONTAINS:
				return (strpos($val2, $val1) !== false);
				break;
			case self::OPERATOR_NOT_CONTAINS:
				return (strpos($val2, $val1) === false);
				break;
		}
		throw new Exception("Invalid operator: $operator");
	}
	
	public static function asString($val) {
		if (is_bool($val)) {
			return ($val === true)? 'true': 'false';
		}
		if (is_numeric($val)) {
			return (string)$val;
		}
		return (string)$val;
	}
	
	private static $operators;
	public static function getOperators() {
		if (self::$operators === null) {
			$reflect = new ReflectionClass('CentralTestAssertion');
			$constants = $reflect->getConstants();
			self::$operators = array();
			foreach ($constants as $name => $value) {
				if (strpos($name, 'OPERATOR_') === 0) {
					self::$operators[] = $value;
				}
			}
		}
		return self::$operators;
	}
	
	protected static $valid_device_operators = array(
		self::OPERATOR_EQUAL,
		self::OPERATOR_EQUAL_LOOSE,
		self::OPERATOR_NOT_EQUAL,
		self::OPERATOR_STARTS_WITH,
		self::OPERATOR_NOT_STARTS_WITH,
		self::OPERATOR_ENDS_WITH,
		self::OPERATOR_CONTAINS,
		self::OPERATOR_NOT_CONTAINS,
	);
	
	protected static $valid_capability_operators = array(
		self::OPERATOR_EQUAL,
		self::OPERATOR_NOT_EQUAL,
		self::OPERATOR_GREATER_THAN,
		self::OPERATOR_LESS_THAN,
		self::OPERATOR_GREATER_THAN_EQUAL,
		self::OPERATOR_LESS_THAN_EQUAL,
		self::OPERATOR_STARTS_WITH,
		self::OPERATOR_NOT_STARTS_WITH,
		self::OPERATOR_ENDS_WITH,
		self::OPERATOR_CONTAINS,
		self::OPERATOR_NOT_CONTAINS,
	);
	
	public function validOperator() {
		switch ($this->assertion_type) {
			case self::TYPE_DEVICE:
				return in_array($this->operator, self::$valid_device_operators);
				break;
			case self::TYPE_CAPABILITY:
			case self::TYPE_VIRTUAL_CAPABILITY:
				return in_array($this->operator, self::$valid_capability_operators);
				break;
		}
		return false;
	}
}
