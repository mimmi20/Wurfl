<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package	WURFL_VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Virtual capability helper
 * @package	WURFL_VirtualCapability
 */
 
class WURFL_VirtualCapability_IsApp extends WURFL_VirtualCapability {

	protected $required_capabilities = array('device_os');

	/**
	 * Simple strings or regex patterns that indicate a UA is from a native app
	 * @var array
	*/
	protected $patterns = array(
			'^Dalvik',
			'Darwin/',
			'CFNetwork',
			'^Windows Phone Ad Client',
			'^NativeHost',
			'^AndroidDownloadManager',
			'-HttpClient',
			'^AppCake',
			'AppEngine-Google',
			'AppleCoreMedia',
			'^AppTrailers',
			'^ChoiceFM',
			'^ClassicFM',
			'^Clipfish',
			'^FaceFighter',
			'^Flixster',
			'^Gold/',
			'^GoogleAnalytics/',
			'^Heart/',
			'^iBrowser/',
			'iTunes-',
			'^Java/',
			'^LBC/3.',
			'Twitter',
			'Pinterest',
			'^Instagram',
			'FBAN',
			'#iP(hone|od|ad)[\d],[\d]#',
			// namespace notation (com.google.youtube)
			'#[a-z]{3,}(?:\.[a-z]+){2,}#',
	);

	protected function compute() {
		$ua = $this->request->userAgent;

		if ($this->device->device_os == "iOS" && !WURFL_Handlers_Utils::checkIfContains($ua, "Safari")) return true;
		foreach ($this->patterns as $pattern) {
			if ($pattern[0] === '#') {
				// Regex
				if (preg_match($pattern, $ua)) return true;
				continue;
			}
				
			// Substring matches are not abstracted for performance
			$pattern_len = strlen($pattern);
			$ua_len = strlen($ua);

			if ($pattern[0] === '^') {
				// Starts with
				if (strpos($ua, substr($pattern, 1)) === 0) return true;

			} else if ($pattern[$pattern_len - 1] === '$') {
				// Ends with
				$pattern_len--;
				$pattern = substr($pattern, 0, $pattern_len);
				if (strpos($ua, $pattern) === ($ua_len - $pattern_len)) return true;

			} else {
				// Match anywhere
				if (strpos($ua, $pattern) !== false) return true;
			}
		}

		return false;
	}
}