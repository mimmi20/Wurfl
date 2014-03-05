ScientiaMobile WURFL PHP API change log:

[2014.01.07] 1.5.1.0
- Added support in Virtual Capabilities for:
	- Internet Explorer 11
	- Windows 8.1
	- OS extraction for Opera Mini User-Agents
- Improved detection of:
	- Android
	- Apple iOS
	- Xbox One
	- Pantech feature phones
- Added new matcher for Desktop Applications
- Optimized matcher order

[2013.10.09] 1.5.0.2
- Bugfix: using an old WURFL with MySQL persistence causes infinte loop

[2013.10.07] 1.5.0.1
- Bugfix: missing capabilities demo page

[2013.10.03] 1.5.0.0
- New Feature: Virtual Capabilities
- Improved normalization
- Improved detection of:
    - Android
    - Apple iOS
    - Windows Phone
    - Firefox Mobile
    - Firefox OS
    - SmartTVs
    - BlackBerry
    - Skyfire
    - Opera Mini
    - UCWEB
    - Robots
    - Native apps

[2013.01.11] 1.4.4.0
- Improved detection of Android 4.1+
- Improved detection of Windows RT
- Simplified detection of Firefox desktop
- Improved detection of Xbox console
- Improved detection of Opera Mobi and Opera Tablet

[2012.11.07] 1.4.3.0
- Improved detection of Kindle Fire
- Improved detection of Playstation Vita
- Improved detection of Windows Phone
- Improved detection of Windows RT
- Improved detection of Safari desktop browser
- Added Device-Stock-UA support
- Improved detection of Maemo devices
- Improved detection of SmartTVs
- Improved detection of XBOX 360
- Improved detection of Epiphany browser
- Updated WURFL database

[2012.09.04] 1.4.2.0
- Improved detection of Mobile Chrome
- Improved detection of Smart TVs
- Improved examples to allow auto-reloading if WURFL data changes
- Bugfix: Improperly normalized filenames on Windows may cause exceptions

[2012.07.30] 1.4.1.1
- Bugfix: Some desktop UAs with "toolbar" are detected as robots in HA mode

[2012.04.07] 1.4.1
- Bugfix: Invalid definition of WURFL_Storage::save()
- Bugfix: Chrome Beta on Android is not detected properly
- Bugfix: Invalid system temp dir returned from sys_get_temp_dir()

[2012.03.30] 1.4.0
- Complete overhaul of all User Agent Handlers
- Improved normalizers
- Added High-Performance vs. High-Accuracy mode
- Added Introspector utility for diagnostics
- Massive code and documentation cleanup
- Added "Secondary Caching" to persistence providers, so memcache and APC can be used
    to improve performance of lookups to the persistence provider
- Refactored examples for simplicity
- Changed recommended configuration from XML Config to InMemory config
- Removed the web patch, as it is now integrated with the main WURFL file

[2011.07.19] 1.3.1
- Enabled displaying errors in example script
- Updated default wurfl.xml
- Added manual lookup script
- Fixed bug with auto-loading WURFL failure on Solaris (EX flock on RO file failed)
- Improved detection of Tablet PC devices
- Improved detection of Android 2.3 - 3.0 devices

[2011.05.14] 1.3.0
- Documented all classes
- Improved Exceptions
- Bugfix: ArrayConfig can now use relative config file pathnames
- Updated unit tests and phing build file

[2010.10.05]
- Added Storage to replace (Persistence & Cache)
- Added NameSpace support for Memcache and Apc
- Added Normalizers for
    - Locale
    - SerialNumbers
    - Maemo
- Added Handler
    - MaemoHandler
- Added Support For Multiple Memcache backend


[2010.05.06]
- Replaced WURFLManagerProvider with WURFLManagerFactory
- Autoreloading Added


[2009.06.01]
- Configuration:
	- Changed the "persistance" to "persistence" (N.B a to e) in wurfl-config.xml
	- Added Possibilty to specify configuration from an array.(array-wurfl-config.php)
- Normalizers
	- Added
		- Android
		- Chrome
		- Opera
		- Safari
		- MSIE	
- Handlers
	- Added 
		- AndroidHandler
		- ChromeHandler
		- BotCrawlerTranscoderHandler		
- Logger
	- Removed dependency from the PEAR Log
	- Added Custom Logger
- WURFLLoader
	- Added A WURFLReloader 
- Created a new web_browsers_patch			

[2010-06-16]
Added Caching expiration capability
