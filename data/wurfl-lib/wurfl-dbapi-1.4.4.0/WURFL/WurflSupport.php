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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides static supporting functions for Tera-WURFL
 * @package TeraWurfl
 *
 */
class WurflSupport{

    /**
     * Formats an int as a human-readable byte size
     * @param int|string $bytes
     * @return string
     */
	public static function formatBytes($bytes){
	    $unim = array("B","KB","MB","GB","TB","PB");
	    $c = 0;
	    while ($bytes>=1024) {
	        $c++;
	        $bytes = $bytes/1024;
	    }
	    return number_format($bytes,($c ? 2 : 0),".",",")." ".$unim[$c];
	}
    /**
     * Formats the given $bytes and $seconds as a bitrate
     * @param int|string $bytes
     * @param int|string $seconds
     * @return string
     */
	public static function formatBitrate($bytes,$seconds){
		$unim = array("bps","Kbps","Mbps","Gbps","Tbps","Pbps");
		$bits = $bytes * 8;
		$bps = $bits / $seconds;
	    $c = 0;
		while ($bps>=1000) {
	        $c++;
	        $bps = $bps/1000;
	    }
	    return number_format($bps,($c ? 2 : 0),".",",")." ".$unim[$c];
	}
    /**
     * Converts boolean values to strings for display
     * @param bool $var
     * @return string
     */
	public static function showBool($var){
		if($var === true)return("true");
		if($var === false)return("false");
		return($var);
	}
    /**
     * Displays the given PHP Log Level as its constant name
     * @param int $num Log level
     * @return string Log level name
     */
	public static function showLogLevel($num){
		$log_arr = array(1=>"LOG_CRIT",4=>"LOG_ERR",5=>"LOG_WARNING",6=>"LOG_NOTICE");
		return($log_arr[$num]);
	}
}
