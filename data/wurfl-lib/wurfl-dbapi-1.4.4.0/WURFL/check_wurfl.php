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
if (!isset($_GET['force_ua'])) {
	$_GET['force_ua'] = 'SonyEricssonK700i/R2AC SEMC-Browser/4.0.2 Profile/MIDP-2.0 Configuration/CLDC-1.1';
}
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
<form action="check_wurfl.php" method="GET">
Mobile device user agent:<br />
<input type="text" name="force_ua" size="100" value="<?php echo $_GET['force_ua']?>">
<input type="submit" name="submit" value="Submit"/>
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
	//TeraWurflConfig::$CACHE_ENABLE = true;
}

$load_class = microtime(true);

// instantiate the Tera-WURFL object
$wurflObj = new TeraWurfl();

$init_class = microtime(true);

// Get the capabilities from the object
$matched = $wurflObj->GetDeviceCapabilitiesFromAgent($_GET['force_ua']);

$end = microtime(true);

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
echo "<h3>Test resulted in $text match</h3>";
echo "<pre>";
var_export(array(
	'original'=>$wurflObj->httpRequest->user_agent->original,
	'cleaned'=>$wurflObj->httpRequest->user_agent->cleaned,
	'normalized'=>$wurflObj->httpRequest->user_agent->normalized,
));
echo "<hr/>\n";
var_export($wurflObj->capabilities);
echo "</pre>";

?>
<form action="check_wurfl.php" method="GET">
Mobile device user agent:<br />
<input type="text" name="force_ua" size="100" value="<?php echo $_GET['force_ua']?>">
<input type="submit" name="submit" value="Submit"/>
</form>
</body>
</html>
