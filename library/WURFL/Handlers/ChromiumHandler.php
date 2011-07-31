<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * ChromeUserAgentHanlder
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WURFL_Handlers_ChromiumHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "CHROMIUM";
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	
	/**
	 * Intercept all UAs Containing Chromium and are not mobile browsers
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isMobileBrowser ( $userAgent )) {
			return false;
		}
		return WURFL_Handlers_Utils::checkIfContains ( $userAgent, "Chromium" )
            && WURFL_Handlers_Utils::checkIfContains ( $userAgent, "Chrome" );
	}
	
	private $chromes = array (
        "" => "google_chrome",
        "1" => "google_chrome_1",
        "2" => "google_chrome_2",
        "3" => "google_chrome_3",
        "4" => "google_chrome_4",
        "5" => "google_chrome_5",
        "6" => "google_chrome_6",
        "7" => "google_chrome_7",
        "8" => "google_chrome_8",
        "9" => "google_chrome_9",
        "10" => "google_chrome_10",
        "11" => "google_chrome_11",
        "12" => "google_chrome_12",
        "13" => "google_chrome_13",
        "14" => "google_chrome_14",
        "15" => "google_chrome_15",
        "16" => "google_chrome_16",
        "17" => "google_chrome_17",
        "18" => "google_chrome_18",
        "19" => "google_chrome_19"
    );
    
    function lookForMatchingUserAgent($userAgent) {
		return $this->applyRecoveryMatch($userAgent);
	}
	
	function applyRecoveryMatch($userAgent) {
		$chromeVersion = $this->chromeVersion ( $userAgent );
		$chromeId = "google_chrome";
		if (isset ( $this->chromes [$chromeVersion] )) {
			$chromeId = $this->chromes [$chromeVersion];
		}
		
		//var_dump($userAgent, $chromeVersion, $this->getPrefix(), $this->userAgentsWithDeviceID);exit;
		if($this->isDeviceExist ( $chromeId )) {
			return $chromeId;
		}

		return "generic_web_browser";
		
	}
	
	const CHROME_VERSION_PATTERN = "/.*Chromium\/(\d+).*/";
	private function chromeVersion($userAgent) {
        if (preg_match ( self::CHROME_VERSION_PATTERN, $userAgent, $match )) {
			return $match [1];
        }
		return NULL;
	}
    /**/
}