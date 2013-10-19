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
 * Standalone utility for deriving device capabilities from a user agent
 * @package WURFL_VirtualCapability
 */
class WURFL_VirtualCapability_UserAgentTool {
	
	/**
	 * Gets a device from the UA
	 * @param string $ua
	 * @return WURFL_VirtualCapability_UserAgentTool_Device
	 */
	public function getDevice($ua) {
		$device = $this->assignProperties(new WURFL_VirtualCapability_UserAgentTool_Device($ua));
		$device->normalize();
		return $device;
	}
	
	/**
	 * Sets the device properties
	 * @param WURFL_VirtualCapability_UserAgentTool_Device $device
	 * @return WURFL_VirtualCapability_UserAgentTool_Device
	 */
	protected function assignProperties($device) {
		//Is UA Android?
		if (strpos($device->ua, 'Android') !== false) {
			$device->os->setRegex('/Android( [0-9]\.[0-9]|)?.*/', 'Android', 1);
			
			//Is Dalvik?
			if (strpos($device->ua, 'Dalvik') !== false) {
				$device->browser->name = 'Android App';
				if ($device->browser->setRegex('/Android ([0-9]\.[0-9])/', null, 1)) return $device;
			}
						
			//Is FB app?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?Android.+?AppleWebKit.+FBAN/', 'FaceBook Android App', $device->os->version)) return $device;			

			//Is UA Chrome Mobile?
			if ($device->browser->setRegex('/Chrome\/([0-9]?[0-9])\.?/', 'Chrome Mobile', 1)) return $device;
			
			//Is UA Fennec?
			if ($device->browser->setRegex('/(?:Firefox|Fennec)\/([0-9]?[0-9]\.[0-9]?)/', 'Firefox Mobile', 1)) return $device;
			
			//Is UA Opera Mobi?
			if ($device->browser->setRegex('/Opera Mobi\/.*Version\/([0-9]?[0-9])/', 'Opera Mobile', 1)) return $device;
			
			//Is UA Opera Mini?
			if ($device->browser->setRegex('/Opera Mini\/([0-9]+)?\.[0-9]/', 'Opera Mini', 1)) return $device;
			
			//Is UA Opera Tablet?
			if ($device->browser->setRegex('/Opera Tablet\/.*Version\/([0-9]?[0-9])/', 'Opera Tablet', 1)) return $device;
			
			//Is UA UC Browser with UCBrowser tag?
			if ($device->browser->setRegex('/UCBrowser\/([0-9]+)\./', 'UC Browser', 1)) return $device;
			
			//Is UA UC Browser with UCWEB tag?
			if ($device->browser->setRegex('/^JUC.*UCWEB([0-9])/', 'UC Browser', 1)) return $device;
			
			//Is UA Amazon Silk browser?
			if ($device->browser->setRegex('/Silk\/([0-9]\.[0-9]).+?Silk\-Accelerated/', 'Amazon Silk Browser', 1)) return $device;
	
			//Is Android Stock Browser?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?Android.+?AppleWeb(?:K|k)it.+?(?:(?:Mobile |)?Safari)/', 
				'Android', $device->os->version)) return $device;
			
			return $device;
		}
		
		//Is UA Amazon Silk browser without the word Android?
		if (strpos($device->ua, 'Silk') !== false && $device->browser->setRegex('/Silk\/([0-9]\.[0-9]).+?Silk\-Accelerated/', 'Amazon Silk Browser', 1) 
			&& $device->os->set("Android", null)) return $device;
		
		//Is UA iOS?
		if (strpos($device->ua, 'iPhone') !== false || strpos($device->ua, 'iPad') !== false || strpos($device->ua, 'iPod') !== false) {
			$device->os->name = 'iOS';
			
			if ($device->os->setRegex('/^Mozilla\/[45]\.[0-9] \((iPhone|iPod|iPad);(?: U;)? CPU(?: iPhone|) OS ([0-9]_[0-9](?:_[0-9])?) like Mac OS X/', 'iOS', 2)) {
				$device->os->version = str_replace("_", ".", $device->os->version);
			}
			
			//Is UA Chrome Mobile on iOS?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?like Mac OS X.+?CriOS\/([0-9]+?)\.[0-9].+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./', 
				'Chrome Mobile iOS', 1)) return $device;
			
			//Is UA UC Web Browser?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+.*UCBrowser\/?([0-9]+)\./', 
				'UC Web Browser on iOS', 1)) return $device;

			//Is UA Mobile iOS Safari?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+.*FBAN/', 'FaceBook app on iPhone', 
				$device->os->version)) return $device;

			//Is UA Mobile iOS Safari?
			if ($device->browser->setRegex('/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./', 'Mobile Safari', 
				$device->os->version)) return $device;

			return $device;
		}
		
		//Is UA Windows Mobile?
		if ($device->os->setContains('Windows CE', 'Windows Mobile') && $device->browser->set('IE Mobile')) return $device;
		
		if (strpos($device->ua, 'Windows Phone') !== false) {
			//Is UA Windows Phone?
			if ($device->os->setRegex('/^Mozilla\/[45].0 \(compatible; MSIE ([0-9]+\.[0-9]); Windows Phone(?: OS)? ([0-9]\.[0-9])/', 'Windows Phone', 2)) {
				$device->browser->set('IE Mobile', $device->os->getLastRegexMatch(1));
				return $device;
			}
			
			// Is UA Windows Phones OS - Loose match?
			if ($device->os->setRegex('/^Mozilla\/[45].0 \(compatible; MSIE ([0-9]+\.[0-9]);.+?Windows Phone(?: OS)? ([0-9]\.[0-9])/', 'Windows Phone', 2)) {
				$device->browser->set('IE Mobile', $device->os->getLastRegexMatch(1));
				return $device;
			}
		}
		
		//Is UA S40 Ovi Browser?
		if (strpos($device->ua, 'OviBrowser') !== false && $device->browser->setRegex('/\bS40OviBrowser\/([0-9]\.[0-9])/', 'S40 Ovi Browser', 1) && $device->os->set('Nokia Series 40')) return $device;
		
		//Is Series60?
		if (strpos($device->ua, 'Symbian') !== false && $device->os->setRegex('/(?:SymbianOS\/([0-9]\.[0-9]).+?Series60\/[0-9]\.?[0-9]?)|(?:Series60\/([0-9]\.?[0-9]?) )|(?:S60\/([0-9]\.?[0-9]?))/', 
			'Symbian S60', 1)) {
			
			if ($device->os->setRegex('/^Mozilla\/[45]\.0 \(Symbian\/3/', 'Symbian', '^3'));
			
			if ($device->browser->setRegex('/NokiaBrowser\/([0-9]\.[0-9])/', 'Symbian S60 Browser', 1)) return $device;

			if ($device->browser->setRegex('/Opera Mobi.+Version\/([0-9]?[0-9]\.[0-9]?[0-9])/', 'Opera Mobi', 1)) return $device;

			$device->browser->set('Symbian S60 Browser');
			return $device;
		}
		
		//Is UA Blackberry?
		if (strpos($device->ua, 'BlackBerry') !== false && $device->os->setRegex('/(?:BlackBerry)|(?:^Mozilla\/5.0 \(BB10; ([a-zA-Z0-9])\))/', 'BlackBerry')) {
			
			if ($device->os->setRegex('/^BlackBerry[0-9A-Za-z]+?\/([0-9]\.[0-9]).+?UC Browser\/?([0-9]\.[0-9])/', null, 1)) {
				$device->browser->set('UC Web', $device->os->getLastRegexMatch(2));
				return $device;
			}
			
			if ($device->os->setRegex('/^UCWEB\/[0-9]\.0.+?; [a-zA-Z][a-zA-Z]?\-[a-zA-Z]?[a-zA-Z]; [0-9]+?\/([0-9]\.[0-9]).+?UCBrowser\/?([0-9]\.[0-9])/', null, 1)) {
				$device->browser->set('UC Web', $device->os->getLastRegexMatch(2));
				return $device;
			}
			
			if ($device->os->setRegex('/^BlackBerry[0-9A-Za-z]+?\/([0-9]\.[0-9])/', null, 1)) {
				$device->browser->set('BlackBerry Browser');
				return $device;
			}
			
			if ($device->os->setRegex('/^Mozilla\/[45]\.0 \(BlackBerry;(?: U;)? BlackBerry.+?Version\/([0-9]\.[0-9])/', null, 1)) {
				$device->browser->set('BlackBerry Browser', $device->os->version);
				return $device;
			}
			
			if ($device->os->setRegex('/^Mozilla\/[45]\.0 \(BB10; .+?Version\/([0-9]\.[0-9])/', null, 1)) {
				$device->browser->set('BlackBerry Webkit Browser', $device->os->version);
				return $device;
			}
			
			// TODO: figure out if we need to return here
		}
		
		//Is UA RIM Tablet OS?
		if (strpos($device->ua, 'RIM Tablet OS') !== false 
			&& $device->os->setRegex('/RIM Tablet OS ([0-9]\.[0-9]).+?Version\/([0-9]\.[0-9])/', 'RIM Tablet OS', 1)) {
			$device->browser->set('RIM OS Browser', $device->os->getLastRegexMatch(2));
			return $device;
		}
		
		//Is UA Netfront?
		if (strpos($device->ua, 'NetFront') !== false 
			&& $device->browser->setRegex('/NetFront\/([0-9]\.[0-9])/', 'NetFront', 1)) return $device;
		
		//Is UA Teleca Obigo
		if ($device->browser->setContains('Obigo', 'Teleca Obigo')
			&& $device->browser->setRegex('/Obig[a-zA-Z]+?\/(Q[0-9\.ABC]+)/', null, 1)) return $device;
		
		//Is UA Samsung's Bada OS?
		if (strpos($device->ua, 'Dolfin') !== false
			&& $device->os->setRegex('/SAMSUNG.+?\bBada\/([0-9]\.[0-9]);?.+Dolfin\/([0-9]\.[0-9])/', 'Bada', 1)) {
			$device->browser->set('Dolfin Browser', $device->os->getLastRegexMatch(2));
			return $device;
		}
		
		//Is UA a MAUI browser?
		if ($device->browser->setContains('MAUI', 'MAUI Browser')) return $device;
		
		//Is UA an Openwave browser?
		if (strpos($device->ua, 'Dolfin') !== false
			&& $device->browser->setRegex('/UP\.(?:Browser|Link)\/([0-9]\.[0-9])/', 'Openwave Browser', 1)) return $device;
		
		//Is UA webOS?
		if ($device->os->setRegex('/^Mozilla\/[45]\.0 \((?:Linux; )?webOS\/([0-9]\.[0-9])/', 'webOS', 1)) {
			$device->browser->set('webOS Browser', $device->os->version);
			return $device;
		}
		
		if (strpos($device->ua, 'Opera') !== false) {
			//Is UA Opera Mobi?
			if ($device->browser->setContains('Opera Mobi', 'Opera Mobile')) {
				if ($device->browser->setRegex('/Opera Mobi.+Version\/([0-9]?[0-9]\.[0-9]?[0-9])/', null, 1)) return $device;
				return $device;
			}
			
			//Is UA Opera Mini?
			if ($device->browser->setRegex('/Opera Mini\/([0-9]\.[0-9])/', 'Opera Mini', 1)) return $device;
			
			//Is UA Opera Sync?
			if ($device->browser->setRegex('/Browser\/Opera Sync\/SyncClient.+?Version\/([0-9]?[0-9]\.[0-9][0-9]?)/', 
				'Opera Link Sync', 1)) return $device;
		}
		
		if (strpos($device->ua, 'Maemo') !== false) {
			$device->os->set('Maemo');
			//Maemo
			if ($device->browser->setRegex('/Maemo.+?Firefox\/([0-9a\.]+) /', 'Firefox', 1)) return $device;
		}
		
		//Final ditch effort
		if ($device->browser->setRegex('/(?:MIDP.+?CLDC)|(?:UNTRUSTED)/', 'Java Applet')) return $device;
		
		
		// Desktop Browsers
		
		//MSIE 
		if (strpos($device->ua, 'MSIE') !== false 
			&& $device->os->setRegex('/^Mozilla\/[0-9]\.0 \(compatible; MSIE ([0-9][0-9]?\.[0-9][0-9]?); ((?:Windows NT [0-9]\.[0-9])|(?:Windows [0-9]\.[0-9])|(?:Windows [0-9]+)|(?:Mac_PowerPC))/', 2)) {
			$device->browser->set('IE', $device->os->getLastRegexMatch(1));
			return $device;
		}
		
		//Yandex Browser
		if (strpos($device->ua, 'YaBrowser') !== false 
			&& $device->os->setRegex('/^Mozilla\/[45]\.[0-9] \((?:Macintosh; )?([a-zA-Z0-9\._ ]+)\) AppleWebKit.+YaBrowser\/([0-9]?[0-9]\.[0-9])/', 1)) {
			$device->browser->set('Yandex browser', $device->os->getLastRegexMatch(2));
			return $device;
		}
		
		if (strpos($device->ua, 'Chrome') !== false) {
			//Chrome Mac
			if ($device->os->setRegex('/^Mozilla\/[0-9]\.0 \(Macintosh;(?: U;)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([0-9]+\.[]0-9]+)\.?/', 1)) {
				$device->browser->set('Chrome', $device->os->getLastRegexMatch(2));
				return $device;
			}
			
			//Chrome
			if ($device->os->setRegex('/^Mozilla\/[0-9]\.0 \((?:Windows;|X11;)?(?: U; )?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([0-9]+\.[]0-9]+)\.?/', 1)) {
				$device->browser->set('Chrome', $device->os->getLastRegexMatch(2));
				return $device;
			}
		}
		
		//Safari
		if (strpos($device->ua, 'Safari') !== false 
			&& $device->os->setRegex('/^Mozilla\/[0-9]\.0 \((?:(?:Windows|Macintosh); (?:U; |WOW64; )?)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Version\/([0-9]+\.[]0-9]+)\.?/', 1)) {
			$device->browser->set('Safari', $device->os->getLastRegexMatch(2));
			return $device;
		}
		
		if (strpos($device->ua, 'Firefox') !== false) {
			//Firefox - Windows
			if ($device->os->setRegex('/^Mozilla\/[0-9]\.0 .+(Windows [0-9A-Za-z \.]+;).+?rv:.+?Firefox\/([0-9]?[0-9]\.[0-9])/', 1)) {
				$device->browser->set('Firefox', $device->os->getLastRegexMatch(2));
				return $device;
			}
			
			//Firefox
			if ($device->os->setRegex('/^Mozilla\/[0-9]\.0 \((?:X11|Macintosh); (?:U; |Ubuntu; |)((?:Intel|PPC|Linux) [a-zA-Z0-9\- \._\(\)]+);.+?rv:.+?Firefox\/([0-9]?[0-9]\.[0-9])/', 1)) {
				$device->browser->set('Firefox', $device->os->getLastRegexMatch(2));
				return $device;
			}
		}
		
		//Opera
		if (strpos($device->ua, 'Opera') !== false 
			&& $device->os->setRegex('/^Opera\/([0-9]?[0-9]\.[0-9][0-9]?) .+?((?:Windows|Linux|PPC|Intel) [a-zA-Z0-9 _\.\-]+) ?;/', 2)) {
			$device->browser->set('Opera', $device->os->getLastRegexMatch(1));
			$device->browser->setRegex('/^Opera\/.+? Version\/([0-9]?[0-9]\.[0-9][0-9]?)/', null, 1);
			return $device;
		}
		
		return $device;
	}
	
}