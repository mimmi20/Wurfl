<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurflAdmin
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflLoader.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser_XMLReader.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser_SimpleXML.php');

error_reporting(E_ALL);
if(TeraWurflConfig::$OVERRIDE_MEMORY_LIMIT) ini_set("memory_limit",TeraWurflConfig::$MEMORY_LIMIT);

$args = parseArgs($_SERVER['argv']);
set_time_limit(60*20);

$base = new TeraWurfl();
if($base->db->connected !== true){
	throw new Exception("Cannot connect to database: ".$base->db->errors[0]);
}
$twversion = $base->release_branch . " " . $base->release_version;
$wurflversion = $base->db->getSetting('wurfl_version');
$lastupdated = date('r',$base->db->getSetting('loaded_date'));

//var_export($args);
if(empty($args) || array_key_exists('help',$args)){
	$usage =<<<EOL

Tera-WURFL $twversion
The command line WURFL updater for Tera-WURFL
Loaded WURFL: $wurflversion
Last Updated: $lastupdated
---------------------------------------
Usage: php cmd_line_admin.php [OPTIONS]

Option                     Meaning
 --help                    Show this message
 --update=<local,remote>   The source of the WURFL file:
                             Update from your local wurfl.xml file:
                               --update=local
                             Update from wurfl.sourceforge.net:
                               --update=remote
 --clearCache              Clear the device cache
 --rebuildCache            Rebuild the device cache by redetecting all
                             cached devices using the current WURFL
 --stats                   Show statistics about the Tera-WURFL Database


EOL;
	echo $usage;
	exit(0);
}

// Parse arguments
$action = null;
if(array_key_exists('update',$args)){
	$source = (string)$args['update'];
	if($source != "local" && $source != "remote"){
		echo "You must specify a source when using the --update option.  Use --help for help\n\n";
		exit(1);
	}
	$action = "update";
}
if(array_key_exists('clearCache',$args)){
	$base->db->createCacheTable();
	echo "Device cache has been cleared.\n";
}
if(array_key_exists('rebuildCache',$args)){
	if($action != "update"){
		$base->db->rebuildCacheTable();
		echo "Device cache has been rebuilt.\n";
	}
}
if(array_key_exists('stats',$args)){
	$config = $base->rootdir."TeraWurflConfig.php";
	$dbtype = str_replace("TeraWurflDatabase_","",get_class($base->db));
	$dbver = $base->db->getServerVersion();
	$mergestats = $base->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Merge');
	$mergestats['bytesize'] = WurflSupport::formatBytes($mergestats['bytesize']);
	$merge = "\n > MERGE
   Rows:    {$mergestats['rows']}
   Devices: {$mergestats['actual_devices']}
   Size:    {$mergestats['bytesize']}\n";
	$index = "";
	$indexstats = $base->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Index');
	if(!empty($indexstats)){
		$indexstats['bytesize'] = WurflSupport::formatBytes($indexstats['bytesize']);
		$index = "\n > INDEX
   Rows:    {$indexstats['rows']}
   Size:    {$indexstats['bytesize']}\n";
	}
	$cachestats = $base->db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Cache');
	$cachestats['bytesize'] = WurflSupport::formatBytes($cachestats['bytesize']);
	$cache = "\n > CACHE
   Rows:    {$cachestats['rows']}
   Size:    {$cachestats['bytesize']}\n";
	$matcherList = $base->db->getMatcherTableList();
	$matchers = array();
	foreach($matcherList as $name){
		$matchers[] = array('name'=>$name,'stats'=>$base->db->getTableStats($name));
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
	exit(0);
}
if(array_key_exists('debug',$args)){
	switch($args['debug']){
		case "constIDgrouped":
			$matcherList = UserAgentFactory::$matchers;
			foreach($matcherList as $matcher){
				$matcherClass = $matcher."UserAgentMatcher";
				$file = $base->rootdir."UserAgentMatchers/{$matcherClass}.php";
				require_once($file);
				$ids = $matcherClass::$constantIDs;
				if(empty($ids)) continue;
				echo "\n$matcherClass\n\t".implode("\n\t",$ids);
			}
			break;
		case "constIDunique":
			$matcherList = UserAgentFactory::$matchers;
			$ids = array();
			foreach($matcherList as $matcher){
				$matcherClass = $matcher."UserAgentMatcher";
				$file = $base->rootdir."UserAgentMatchers/{$matcherClass}.php";
				require_once($file);
				$ids = array_merge($ids,$matcherClass::$constantIDs);
			}
			$ids = array_unique($ids);
			sort($ids);
			echo implode("\n",$ids);
			break;
		case "createProcs":
			echo "Recreating Procedures.\n";
			$base->db->createProcedures();
			echo "Done.\n";
			break;
		case "batchLookupFallback":
			$raw = file_get_contents($args['file']);
			$ids = preg_split('/[\n\r]+/',$raw);
			unset($raw);
			foreach($ids as $id){
				$fallback = array();
				if($base->db->db_implements_fallback){
					$tree = $base->db->getDeviceFallBackTree($id);
					foreach($tree as $node) $fallback[]=$node['id'];
				}else{
					die("Unsupported on this platform\n");
				}
				echo implode(', ',$fallback)."\n";
			}
			break;
	}
}
if(is_null($action)){
	echo "\n";
	exit(0);
}

$newfile = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE.".zip";
$wurflfile = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE;

if($source == "remote" || $source == "remote_cvs"){
	if($source == "remote"){
		$dl_url = TeraWurflConfig::$WURFL_DL_URL; 
	}elseif($source == "remote_cvs"){
		$dl_url = TeraWurflConfig::$WURFL_CVS_URL;
	}
	echo "Downloading WURFL from $dl_url ...\n\n";
	flush();
	if(!file_exists($newfile) && !is_writable($base->rootdir.TeraWurflConfig::$DATADIR)){
		$base->toLog("Cannot write to data directory (permission denied)",LOG_ERR);
		Throw New Exception("Fatal Error: Cannot write to data directory (permission denied). (".$base->rootdir.TeraWurflConfig::$DATADIR.")\n\nPlease make the data directory writable by the user or group that runs the webserver process, in Linux this command would do the trick if you're not too concerned about security: chmod -R 777 ".$base->rootdir.TeraWurflConfig::$DATADIR);
		exit(1);
	}
	if(file_exists($newfile) && !is_writable($newfile)){
		$base->toLog("Cannot overwrite WURFL file (permission denied)",LOG_ERR);
		Throw New Exception("Fatal Error: Cannot overwrite WURFL file (permission denied). (".$base->rootdir.TeraWurflConfig::$DATADIR.")\n\nPlease make the data directory writable by the user or group that runs the webserver process, in Linux this command would do the trick if you're not too concerned about security: chmod -R 777 ".$base->rootdir.TeraWurflConfig::$DATADIR);
		exit(1);
	}
	// Download the new WURFL file and save it in the DATADIR as wurfl.zip
	@ini_set('user_agent', "PHP/Tera-WURFL_$version");
	$download_start = microtime(true);
	if(!$gzdata = file_get_contents($dl_url)){
		Throw New Exception("Error: Unable to download WURFL file from ".TeraWurflConfig::$WURFL_DL_URL);
		exit(1);
	}
/*	$destination=fopen($newfile,"w"); 
	$source=fopen($dl_url,"r"); 
	while ($block=fread($source,256*1024)) fwrite($destination,$block);
	fclose($source);
	fclose($destination);
*/
	$download_time = microtime(true) - $download_start;
	file_put_contents($newfile,$gzdata);
	$gzsize = WurflSupport::formatBytes(filesize($newfile));
	// Try to use ZipArchive, included from 5.2.0
	if(class_exists("ZipArchive")){
		$zip = new ZipArchive();
		if ($zip->open(str_replace('\\','/',$newfile)) === TRUE) {
			$zip->extractTo(str_replace('\\','/',dirname($wurflfile)),array('wurfl.xml'));
			$zip->close();
		} else {
			Throw New Exception("Error: Unable to extract wurfl.xml from downloaded archive: $newfile");
			exit(1);
		}
	}else{
		system("gunzip $newfile");
	}
	$size = WurflSupport::formatBytes(filesize($wurflfile))." [$gzsize compressed]";
	$download_rate = WurflSupport::formatBitrate(filesize($newfile), $download_time);
	$ok = true;
	echo "done ($wurflfile: $size)\nDownloaded in $download_time sec @ $download_rate \n\n";
	usleep(50000);
	flush();
}

$loader = new TeraWurflLoader($base);
//$ok = $base->db->initializeDB();
$ok = $loader->load();
if($ok){
	echo "Database Update OK\n";
	echo "Total Time: ".$loader->totalLoadTime()."\n";
	echo "Parse Time: ".$loader->parseTime()." (".$loader->getParserName().")\n";
	echo "Validate Time: ".$loader->validateTime()."\n";
	echo "Sort Time: ".$loader->sortTime()."\n";
	echo "Patch Time: ".$loader->patchTime()."\n";
	echo "Database Time: ".$loader->databaseTime()."\n";
	echo "Cache Rebuild Time: ".$loader->cacheRebuildTime()."\n";
	echo "Number of Queries: ".$base->db->numQueries."\n";
	if(version_compare(PHP_VERSION,'5.2.0') === 1){
		echo "PHP Memory Usage: ".WurflSupport::formatBytes(memory_get_usage())."\n";
	}
	echo "--------------------------------\n";
	echo "WURFL Version: ".$loader->version." (".$loader->last_updated.")\n";
	echo "WURFL Devices: ".$loader->mainDevices."\n";
	echo "PATCH New Devices: ".$loader->patchAddedDevices."\n";
	echo "PATCH Merged Devices: ".$loader->patchMergedDevices."\n";
}else{
	echo "ERROR LOADING DATA!\n";
	echo "Errors: \n\n";
	echo htmlspecialchars(var_export($loader->errors,true));
}

/**
 * Command Line Interface (CLI) options parser
 * @author pwfisher
 * @see http://pwfisher.com/nucleus/index.php?itemid=45
 * @param array Raw command line arguments
 */
function parseArgs($argv){
	array_shift($argv); $o = array();
	foreach ($argv as $a){
		if (substr($a,0,2) == '--'){ $eq = strpos($a,'=');
			if ($eq !== false){ $o[substr($a,2,$eq-2)] = substr($a,$eq+1); }
			else { $k = substr($a,2); if (!isset($o[$k])){ $o[$k] = true; } } }
		else if (substr($a,0,1) == '-'){
			if (substr($a,2,1) == '='){ $o[substr($a,1,1)] = substr($a,3); }
			else { foreach (str_split(substr($a,1)) as $k){ if (!isset($o[$k])){ $o[$k] = true; } } } }
		else { $o[] = $a; } }
	return $o;
}
