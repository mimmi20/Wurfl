<?php
require_once realpath(dirname(__FILE__).'/TeraWurflUpdater.php');
class TeraWurflCLI {
	
	/**
	 * @var TeraWurflCLIArgumentCollection
	 */
	protected $arguments;
	/**
	 * @var TeraWurfl
	 */
	protected $wurfl;
	
	public function __construct($wurfl=null) {
		error_reporting(E_ALL);
		$this->arguments = TeraWurflCLIArgumentFactory::createArgumentCollection();
		$this->requireAdditionalClasses();
		if ($wurfl !== null) {
			$this->wurfl = $wurfl;
		} else {
			$this->createWurflClass();
		}
		$this->checkDb();
	}
	
	public function processArguments() {
		if ($this->arguments->isEmpty()) {
			$this->arguments->add(new TeraWurflCLIArgument('help'));
		}
		foreach ($this->arguments as $arg) {
			$action = 'action'.ucfirst($arg->command);
			if (!method_exists($this, $action)) {
				continue;
			}
			$this->$action($arg);
		}
	}
	
	protected function actionUpdate(TeraWurflCLIArgument $arg) {
		$update_source = $arg->value;
		if ($update_source != 'local' && $update_source != 'remote') {
			throw new TeraWurflCLIInvalidArgumentException("You must specify a valid source when using the --update option.  Use --help for help\n\n");
		}
		$updater = new TeraWurflUpdater($this->wurfl, $update_source);
		$updater->setVerbose();
		$force_update = $this->arguments->exists('force');
		if ($update_source == 'remote') {
			try {
				$available = $updater->isUpdateAvailable();
			} catch(Exception $e) {
				echo "Unable to check if update is available, assuming it is.\n";
				$available = true;
			}
			if (!$force_update && !$available) {
				echo "Use --force to force an update.\n";
				return;
			}
		}
		
		try {
			$status = $updater->update();
		} catch (TeraWurflUpdateDownloaderException $e) {
			echo "\n".$e->getMessage()."\n";
			if ($updater->downloader->download_url == 'http://downloads.sourceforge.net/project/wurfl/WURFL/latest/wurfl-latest.zip') {
				echo <<<EOL

The license of the WURFL Data has recently changed. ScientiaMobile 
has temporarily moved the WURFL download location to ensure that you 
are aware of the changes.  Please download the wurfl.xml file manually 
and place it in the data/ directory, then run --update=local
EOL;
			}
			return;
		}
		
		if ($status) {
			echo "Database Update OK\n";
			echo "Total Time: ".$updater->loader->totalLoadTime()."\n";
			echo "Parse Time: ".$updater->loader->parseTime()." (".$updater->loader->getParserName().")\n";
			echo "Validate Time: ".$updater->loader->validateTime()."\n";
			echo "Sort Time: ".$updater->loader->sortTime()."\n";
			echo "Patch Time: ".$updater->loader->patchTime()."\n";
			echo "Database Time: ".$updater->loader->databaseTime()."\n";
			echo "Cache Rebuild Time: ".$updater->loader->cacheRebuildTime()."\n";
			echo "Number of Queries: ".$this->wurfl->db->numQueries."\n";
			if(version_compare(PHP_VERSION,'5.2.0') === 1){
				echo "PHP Memory Usage: ".WurflSupport::formatBytes(memory_get_usage())."\n";
			}
			echo "--------------------------------\n";
			echo "WURFL Version: ".$updater->loader->version." (".$updater->loader->last_updated.")\n";
			echo "WURFL Devices: ".$updater->loader->mainDevices."\n";
			echo "PATCH New Devices: ".$updater->loader->patchAddedDevices."\n";
			echo "PATCH Merged Devices: ".$updater->loader->patchMergedDevices."\n";
			if(count($updater->loader->errors) > 0){
				echo "\nThe following errors were encountered:\n";
				foreach($updater->loader->errors as $error) echo " * $error\n";
			}
		} else {
			echo "ERROR LOADING DATA!\n";
			echo "Errors: \n\n";
			foreach($updater->loader->errors as $error) echo "$error\n";
		}
	}
	
	protected function actionClearCache(TeraWurflCLIArgument $arg) {
		$this->wurfl->db->createCacheTable();
		echo "Device cache has been cleared.\n";
	}
	
	protected function actionRebuildCache(TeraWurflCLIArgument $arg) {
		$this->wurfl->db->rebuildCacheTable();
					echo "Device cache has been rebuilt.\n";
	}
	
	protected function actionCentralTest(TeraWurflCLIArgument $arg) {
		$test_type = $arg->value;
		require_once dirname(__FILE__).'/../test/CentralTestManager.php';
		$centralTest = new CentralTestManager();
		if ($this->arguments->introspector) {
			if ($this->arguments->username && $this->arguments->password) {
				$centralTest->useIntrospector($this->arguments->introspector->value, $this->arguments->username->value, $this->arguments->password->value);
			} else {
				$centralTest->useIntrospector($this->arguments->introspector->value);
			}
		}
		$centralTest->show_success = false;
		if (preg_match('#(single/.*)$#', $test_type, $matches)) {
			$centralTest->runSingleTest($matches[1]);
		} else {
			$centralTest->runBatchTest($test_type);
		}
	}
	
	protected function actionStats(TeraWurflCLIArgument $arg) {
		$twversion = $this->wurfl->release_branch . " " . $this->wurfl->release_version;
		$wurflversion = $this->wurfl->db->getSetting('wurfl_version');
		$lastupdated = date('r',$this->wurfl->db->getSetting('loaded_date'));
		$config = $this->wurfl->rootdir."TeraWurflConfig.php";
		$dbtype = str_replace("TeraWurflDatabase_","",get_class($this->wurfl->db));
		$dbver = $this->wurfl->db->getServerVersion();
		$mergestats = $this->wurfl->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Merge');
		$mergestats['bytesize'] = WurflSupport::formatBytes($mergestats['bytesize']);
		$merge = "\n > MERGE
   Rows:    {$mergestats['rows']}
   Devices: {$mergestats['actual_devices']}
   Size:    {$mergestats['bytesize']}\n";
		$index = "";
		$indexstats = $this->wurfl->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Index');
		if(!empty($indexstats)){
			$indexstats['bytesize'] = WurflSupport::formatBytes($indexstats['bytesize']);
			$index = "\n > INDEX
   Rows:    {$indexstats['rows']}
   Size:    {$indexstats['bytesize']}\n";
		}
		$cachestats = $this->wurfl->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Cache');
		$cachestats['bytesize'] = WurflSupport::formatBytes($cachestats['bytesize']);
		$cache = "\n > CACHE
   Rows:    {$cachestats['rows']}
   Size:    {$cachestats['bytesize']}\n";
		$matcherList = $this->wurfl->db->getMatcherTableList();
		$matchers = array();
		foreach($matcherList as $name){
			$matchers[] = array('name'=>$name,'stats'=>$this->wurfl->db->getTableStats($name));
		}
		$out =<<<EOF
Tera-WURFL $twversion
Database Type: $dbtype (ver $dbver)
Loaded WURFL: $wurflversion
Last Updated: $lastupdated
Config File: $config
---------- Table Stats -----------
{$merge}{$index}{$cache}
EOF;
		echo $out;
	}
	
	protected function actionDebug(TeraWurflCLIArgument $arg) {
		switch($arg->value){
			case "constIDgrouped":
				$matcherList = WurflConstants::$matchers;
				foreach($matcherList as $matcher){
					$matcherClass = $matcher."UserAgentMatcher";
					$file = $this->wurfl->rootdir."UserAgentMatchers/{$matcherClass}.php";
					require_once($file);
					$properties = get_class_vars($matcherClass);
					if(empty($properties['constantIDs'])) continue;
					echo "\n$matcherClass\n\t".implode("\n\t",$properties['constantIDs']);
				}
				break;
			case "constIDunique":
				$matcherList = WurflConstants::$matchers;
				$ids = array();
				foreach($matcherList as $matcher){
					$matcherClass = $matcher."UserAgentMatcher";
					$file = $this->wurfl->rootdir."UserAgentMatchers/{$matcherClass}.php";
					require_once($file);
					$properties = get_class_vars($matcherClass);
					$ids = array_merge($ids,$properties['constantIDs']);
				}
				$ids = array_unique($ids);
				sort($ids);
				echo implode("\n",$ids);
				break;
			case "constIDsanity":
				$matcherList = WurflConstants::$matchers;
				$errors = 0;
				foreach($matcherList as $matcher){
					$matcherClass = $matcher."UserAgentMatcher";
					$file = $this->wurfl->rootdir."UserAgentMatchers/{$matcherClass}.php";
					require_once($file);
					$properties = get_class_vars($matcherClass);
					if(empty($properties['constantIDs'])) continue;
					foreach ($properties['constantIDs'] as $key => $value) {
						$deviceID = is_null($value)? $key: $value;
						try {
							$this->wurfl->db->getDeviceFromID($deviceID);
						} catch (Exception $e) {
							$errors++;
							echo "Error: $matcherClass references an invalid WURFL ID: $deviceID\n";
						}
					}
				}
				if ($errors === 0) {
					echo "Done. No errors detected.\n";
				} else {
					echo "Done. $errors error(s) detected.\n";
				}
				break;
			case "createProcs":
				echo "Recreating Procedures.\n";
				$this->wurfl->db->createProcedures();
				echo "Done.\n";
				break;
			case "benchmark":
				$quiet = true;
			case "batchLookup":
				if(!isset($quiet)) $quiet = false;
				$fh = fopen($this->arguments->file->value,'r');
				$i = 0;
				$start = microtime(true);
				while(($ua = fgets($fh, 258)) !== false){
					$ua = rtrim($ua);
					$this->wurfl->getDeviceCapabilitiesFromAgent($ua);
					if(!$quiet){
						echo $ua."\n";
						echo $this->wurfl->capabilities['id'].": ".$this->wurfl->capabilities['product_info']['brand_name']." ".$this->wurfl->capabilities['product_info']['model_name']."\n\n";
					}
					$i++;
				}
				fclose($fh);
				$duration = microtime(true) - $start;
				$speed = round($i/$duration,2);
				echo "--------------------------\n";
				echo "Tested $i devices in $duration sec ($speed/sec)\n";
				if(!$quiet) echo "*printing the UAs is very time-consuming, use --debug=benchmark for accurate speed testing\n";
				break;
			case "batchLookupUndetected":
				$fh = fopen($this->arguments->file->value,'r');
				$i = 0;
				$start = microtime(true);
				while(($line = fgets($fh, 1024)) !== false){
					if (strpos($line, "\t") !== false) {
						list($ua,$uaprof) = @explode("\t", $line);
					} else {
						$ua = rtrim($line);
					}
					$this->wurfl->getDeviceCapabilitiesFromAgent($ua);
					$m = $this->wurfl->capabilities['tera_wurfl']['match_type'];
					if ($m == 'recovery' || $m == 'none') {
						echo $line;
					}
					$i++;
				}
				fclose($fh);
				$duration = microtime(true) - $start;
				$speed = round($i/$duration,2);
				echo "--------------------------\n";
				echo "Tested $i devices in $duration sec ($speed/sec)\n";
				break;
			case "batchLookupFallback":
				$ids = file($this->arguments->file->value,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				foreach($ids as $id){
					$fallback = array();
					if($this->wurfl->db->db_implements_fallback){
						$tree = $this->wurfl->db->getDeviceFallBackTree($id);
						foreach($tree as $node) $fallback[]=$node['id'];
					}else{
						die("Unsupported on this platform\n");
					}
					echo implode(', ',$fallback)."\n";
				}
				break;
			case "dumpBuckets":
				echo "Database API v{$this->wurfl->release_version}; ".$this->wurfl->getSetting(TeraWurfl::$SETTING_WURFL_VERSION)."\n";
				$this->wurfl->dumpBuckets();
				break;
		}
	}
	
	protected function actionHelp(TeraWurflCLIArgument $arg) {
		$twversion = $this->wurfl->release_branch . " " . $this->wurfl->release_version;
		$wurflversion = $this->wurfl->db->getSetting('wurfl_version');
		$lastupdated = date('r',$this->wurfl->db->getSetting('loaded_date'));
		$usage =<<<EOL

ScientiaMobile DB API $twversion
Command Line Interface
Loaded WURFL: $wurflversion
Last Updated: $lastupdated
---------------------------------------
Usage: php cmd_line_admin.php [OPTIONS]

Option                     Meaning
 --help                    Show this message
 --update=<local,remote>   Update WURFL data:
                             Update from your local wurfl.xml file:
                               --update=local
                             Update the WURFL data from ScientiaMobile:
                               --update=remote
 --force                   Force an update even if the WURFL data
                             is up to date
 --clearCache              Clear the device cache
 --rebuildCache            Rebuild the device cache by redetecting all
                             cached devices using the current WURFL
 --stats                   Show statistics about the Database API
 --centralTest=<unit|regression|all|single/<test_name>>
                           Run tests from the ScientiaMobile Central
                             testing repository.

EOL;
		echo $usage;
	}
	
	protected function requireAdditionalClasses() {
		if (!$this->arguments->require) return;
		require_once $this->arguments->require->value;
	}
	
	protected function createWurflClass() {
		if ($this->arguments->altClass) {
			$class_name = $this->arguments->altClass->value;
			if (class_exists($class_name, false) && is_subclass_of($class_name, 'TeraWurfl')) {
				$this->wurfl = new $class_name();
			} else {
				throw new TeraWurflCLIInvalidArgumentException("Error: $class_name must extend TeraWurfl.");
			}
		} else {
			$dbconnector = 'TeraWurflDatabase_'.TeraWurflConfig::$DB_CONNECTOR;
			if (!call_user_func(array($dbconnector, 'extensionLoaded'))) {
				die("You do not have the PHP extensions required to use the database connector $dbconnector.  If you are using the default MySQL5 connector, you need the PHP 'mysqli' extension loaded.\n");
			}
			$this->wurfl = new TeraWurfl();
		}
	}
	
	protected function checkDb() {
		if ($this->wurfl->db->connected !== true) {
			throw new Exception("Cannot connect to database: ".$this->wurfl->db->errors[0]);
		}
	}
}
class TeraWurflCLIArgumentFactory {
	/**
	 * @return TeraWurflCLIArgumentCollection
	 */
	public static function createArgumentCollection() {
		$argv = $_SERVER['argv'];
		array_shift($argv);
		$collection = new TeraWurflCLIArgumentCollection();
		foreach ($argv as $raw_arg) {
			$collection->add(self::createArgument($raw_arg));
		}
		return $collection;
	}
	/**
	 * @param string $text Raw argument from ARGV
	 * @return TeraWurflCLIArgument
	 * @throws TeraWurflCLIInvalidArgumentException
	 */
	public static function createArgument($text) {
		if (preg_match('/^(?:-+)?([^=]+)=(.*)$/', $text, $matches)) {
			return new TeraWurflCLIArgument($matches[1], $matches[2]);
		} else if(preg_match('/^(?:-+)?(.*)$/', $text, $matches)) {
			return new TeraWurflCLIArgument($matches[1]);
		} else {
			throw new TeraWurflCLIInvalidArgumentException("Invalid argument: $text");
		}
	}
}
class TeraWurflCLIArgument {
	public $command;
	public $value;
	public function __construct($command, $value=null) {
		$this->command = $command;
		$this->value = $value;
	}
}
class TeraWurflCLIArgumentCollection implements Iterator {
	private $arguments = array();
	private $position = 0;
	public function __get($key) {
		foreach ($this->arguments as $arg) {
			if ($arg->command == $key) return $arg;
		}
		return null;
	}
	public function exists($key) {
		return ($this->__get($key) !== null);
	}
	public function count() { return count($this->arguments); }
	public function isEmpty() { return ($this->count() === 0); }
	public function add(TeraWurflCLIArgument $arg) { $this->arguments[] = $arg; }
	public function rewind() { $this->position = 0; }
	public function current() { return $this->arguments[$this->position]; }
	public function key() { return $this->position; }
	public function next() { ++$this->position; }
	public function valid() { return isset($this->arguments[$this->position]); }
}
