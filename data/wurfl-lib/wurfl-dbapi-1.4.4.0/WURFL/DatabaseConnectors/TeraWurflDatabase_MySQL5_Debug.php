<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL_Database
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides connectivity from Tera-WURFL to MySQL 5
 * @package TeraWurflDatabase
 * @see TeraWurflDatabase
 * @see TeraWurflDatabase_MySQL5_NestedSet
 * @see TeraWurflDatabase_MySQL5_Profiling
 */
include_once dirname(__FILE__).'/TeraWurflDatabase_MySQL5.php';
class TeraWurflDatabase_MySQL5_Debug extends TeraWurflDatabase_MySQL5 {

	public static $match_debug = array();

	public function getDebugInfo() {
		// Used to avoid errors with $class_name::$static_name in PHP < 5.3
		return self::$match_debug;
	}

	public function getDeviceFromUA_RIS($userAgent,$tolerance,UserAgentMatcher &$matcher) {
		$result = parent::getDeviceFromUA_RIS($userAgent,$tolerance,$matcher);
		$device_list = array();
		$max_match_len = 0;
		if ($result != WurflConstants::NO_MATCH) {
			$query = "
SELECT mrg.deviceID, mrg.user_agent 
FROM `".TeraWurflConfig::$TABLE_PREFIX.'Index'."` idx 
	INNER JOIN `".TeraWurflConfig::$TABLE_PREFIX.'Merge'."` mrg ON idx.DeviceID = mrg.DeviceID 
WHERE mrg.match = 1 
	AND idx.matcher = ".$this->SQLPrep($matcher->tableSuffix())."
	AND mrg.user_agent LIKE ".$this->SQLPrep(substr($userAgent,0,$tolerance).'%');
			$res = $this->dbcon->query($query) or die($this->dbcon->error);
			while ($row = $res->fetch_assoc()) {
				$diff_index = 0;
				$diff_substr = '';
				// Start at length of shortest string
				$start_len = (strlen($row['user_agent']) < strlen($userAgent))? strlen($row['user_agent']): strlen($userAgent);
				for ($i=$start_len; $i>0; $i--) {
					if (strcmp(substr($userAgent,0,$i),substr($row['user_agent'],0,$i)) === 0) {
						// $i == current length, $i-1 == current char index
						$diff_index = $i - 1;
						$diff_substr = substr($userAgent,0,$i);
						break;
					}
				}
				if ($i > $max_match_len) $max_match_len = $i;
				$device_list[] = array(
					'device_id' => $row['deviceID'],
					'user_agent' => $row['user_agent'],
					'diff_index' => $diff_index,
					'diff_substr' => $diff_substr,
				);
			}
			$res = null;
			// Sort $device_list array by the device's diff_index
			usort($device_list,array($this,'cmpDiffIndex'));
		}
		self::$match_debug[] = array(
			'matcher' => get_class($matcher),
			'user_agent' => $userAgent,
			'tolerance' => $tolerance,
			'tolerance_ua' => substr($userAgent,0,$tolerance),
			'max_match_len' => $max_match_len,
			'method' => 'RIS',
			'match_id' => $result,
			'device_list' => $device_list,
		);
		return $result;
	}
	
	private function cmpDiffIndex($a, $b) {
		if ($a['diff_index'] === $b['diff_index']) {
			return 0;
		}
		return ($a['diff_index'] > $b['diff_index'])? -1: 1;
	}
}


