<?php
/**
 * Copyright (c) 2013 ScientiaMobile, Inc.
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
if (!isset($_GET['force_ua'])) {
	$_GET['force_ua'] = 'SonyEricssonK700i/R2AC SEMC-Browser/4.0.2 Profile/MIDP-2.0 Configuration/CLDC-1.1';
}

$start = microtime(true);

/**
 * @param string $ua
 * @param int $length
 * @param bool $highlight
 * @param string $class
 * @return string
*/
function niceUA($ua,$length=0,$highlight=false,$class="debug-match") {
	if ($highlight === false) {
		return htmlspecialchars(substr($ua,0,($length == 0)? strlen($ua): $length));
	}
	$first = '<span class="'.$class.'">'.htmlspecialchars(substr($ua,0,$length)).'</span>';
	$last = htmlspecialchars(substr($ua,$length));
	return $first.$last;
}

// Include the Tera-WURFL file
require_once realpath(dirname(__FILE__).'/TeraWurfl.php');
if (preg_match('/Debug$/', TeraWurflConfig::$DB_CONNECTOR)) {
	$debug = true;
	TeraWurflConfig::$CACHE_ENABLE = false;
} else {
	$debug = false;
}

$load_class = microtime(true);

// instantiate the Tera-WURFL object
$wurflObj = new TeraWurfl();

if (isset($_GET['match-mode'])) {
	$current_mode = $_GET['match-mode'];
	TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE = ($current_mode == 'performance');
} else {
	$current_mode = TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE? 'performance': 'accuracy';
}

$init_class = microtime(true);

// Get the capabilities from the object
$matched = $wurflObj->getDeviceCapabilitiesFromAgent($_GET['force_ua']);

$end = microtime(true);

?>
<html>
<head>
<title>check_wurfl</title>
<style>
.debug-tolerance {
	font-weight: bold;
	color: #D00;
}
.debug-match {
	font-weight: bold;
	color: #0D0;
}

</style>
</head>
<body>
<h2>ScientiaMobile Database API <?php echo $wurflObj->release_version; ?></h2>
<form method="GET">
Mobile device user agent:<br />
<input type="text" name="force_ua" style="width: 80%" value="<?php echo $_GET['force_ua']?>">
<input type="submit" name="submit" value="Submit"/><br/>
<label><input type="radio" name="match-mode" value="performance" <?php echo ($current_mode=='performance')? 'checked="checked"': ''; ?>/> High Performance</label>&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="match-mode" value="accuracy" <?php echo ($current_mode=='performance')? '': 'checked="checked"'; ?>/> High Accuracy</label>
</form>
Try some of these:
<ul>
  <li><pre>SonyEricssonK700i/R2AC SEMC-Browser/4.0.2 Profile/MIDP-2.0 Configuration/CLDC-1.1</pre></li>
  <li><pre>MOT-T720/S_G_05.30.0CR MIB/2.0 Profile/MIDP-1.0 Configuration/CLDC-1.0</pre></li>
  <li><pre>SAGEM-myX5-2/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.0 UP.Browser/6.2.2.6.d.2 (GUI) MMP/1.0</pre></li>
  <li><pre>NokiaN90-1/3.0541.5.2 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1</pre></li>
</ul>
<hr size="1" />
<?php

// Get code commit info
if (file_exists(dirname(__FILE__).'/.git/')) {
	$old_dir = getcwd();
	chdir(dirname(__FILE__));
	$code_branch = trim(@shell_exec('git rev-parse --abbrev-ref HEAD'));
	$code_commit = trim(@shell_exec('git rev-parse --verify HEAD'));
	chdir($old_dir);
} else {
	$code_branch = null;
	$code_commit = null;
}

$wurfl_version = $wurflObj->getSetting(TeraWurfl::$SETTING_WURFL_VERSION);
$wurfl_loaded = $wurflObj->getSetting(TeraWurfl::$SETTING_LOADED_DATE);
$wurfl_patches = $wurflObj->getSetting(TeraWurfl::$SETTING_PATCHES_LOADED);

echo "Time to load TeraWurfl.php:".($load_class-$start)."<br>\n";
echo "Time to initialize class:".($init_class-$load_class)."<br>\n";
echo "Time to find the user agent:".($end-$init_class)."<br>\n";
echo "Total:".($end-$start)."<br>\n";
$cached = ($wurflObj->foundInCache)? "<strong>(Found in cache)</strong>": "";
echo "<br>Total Queries: ".$wurflObj->db->numQueries." $cached<br>\n";

if ($debug === true) {
	echo "<h3>Match Debug Info</h3><pre>";
	$debug = $wurflObj->db->getDebugInfo();
	for ($i=0;$i<count($debug);$i++) {
		$matched_id = ($debug[$i]['match_id'] == 'generic')? '[NO MATCH AT THIS STEP]': $debug[$i]['match_id'];
		echo "<h2>Step ".($i+1).":</h2>\n";
		echo "<strong>User Agent:</strong> ".htmlspecialchars($debug[$i]['user_agent'])."\n";
		echo "<strong>Matcher:</strong> {$debug[$i]['matcher']}\n";
		echo "<strong>Method:</strong> {$debug[$i]['method']}\n";
		echo "<strong>Tolerance:</strong> {$debug[$i]['tolerance']}\n";
		echo "<strong>User Agent at Tolerance:</strong> {$debug[$i]['tolerance_ua']}\n";
		echo "<strong>Matched ID:</strong> $matched_id\n";
		if (count($debug[$i]['device_list']) > 0) {
			echo "<strong>User Agent Match Pool</strong>\n";
			echo '<strong>';
			printf('%-48s', "Reference User Agent:");
			echo '</strong>';
			echo niceUA($debug[$i]['user_agent'],$debug[$i]['max_match_len'],true)."\n";
			for ($a=0;$a<count($debug[$i]['device_list']);$a++) {
				$dev = $debug[$i]['device_list'][$a];
				printf('%-48s',$dev['device_id']);
				echo niceUA($dev['user_agent'],$dev['diff_index']+1,true,"debug-match")."\n";
			}
		}
	}
	echo "</pre>";
}

$text = ($matched)?"a conlusive":"an <font color=\"#990000\">inconclusive</font>";
echo "<h3>Test resulted in $text match<br/>";
echo "Matched Device: {$wurflObj->capabilities['id']} - {$wurflObj->capabilities['product_info']['brand_name']} {$wurflObj->capabilities['product_info']['model_name']} ({$wurflObj->capabilities['product_info']['marketing_name']})</h3>";
echo "<pre>";
var_export(array(
	'original'=>$wurflObj->httpRequest->user_agent->original,
	'cleaned'=>$wurflObj->httpRequest->user_agent->cleaned,
	'normalized'=>$wurflObj->httpRequest->user_agent->normalized,
));
echo "</pre><hr/>\n";

$version = "Database API $wurflObj->release_version";
if ($code_branch && $code_commit) {
	$version .= " ($code_branch/$code_commit)";
}
echo "
<strong>API Version</strong>: $version<br>
<strong>WURFL Version</strong>: $wurfl_version<br>
<strong>WURFL Loaded On</strong>: ".date('Y-m-d H:m:s', $wurfl_loaded)."<br>
<strong>WURFL Patches</strong>: $wurfl_patches<br>
<strong>Match Mode</strong>: high-$current_mode<br/>
<strong>WURFL ID</strong>: {$wurflObj->capabilities['id']}<br/>
<strong>User Agent</strong>: {$wurflObj->capabilities['user_agent']}<br/>
<strong>Fall Back</strong>: {$wurflObj->capabilities['fall_back']}<br/>
<strong>Diagnostics Info</strong>: <pre>";
var_export($wurflObj->capabilities['tera_wurfl']);
echo "</pre><hr/>\n";

echo "<h3>Virtual Capabilities</h3><pre>";
var_export($wurflObj->getAllVirtualCapabilities());
echo "</pre>";

echo "<h3>Full Capabilities</h3><pre>";
var_export($wurflObj->capabilities);
echo "</pre>";

?>
<form method="GET">
Mobile device user agent:<br />
<input type="text" name="force_ua" style="width: 80%" value="<?php echo $_GET['force_ua']?>">
<input type="submit" name="submit" value="Submit"/><br/>
<label><input type="radio" name="match-mode" value="performance" <?php echo ($current_mode=='performance')? 'checked="checked"': ''; ?>/> High Performance</label>&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="radio" name="match-mode" value="accuracy" <?php echo ($current_mode=='performance')? '': 'checked="checked"'; ?>/> High Accuracy</label>
</form>
</body>
</html>
