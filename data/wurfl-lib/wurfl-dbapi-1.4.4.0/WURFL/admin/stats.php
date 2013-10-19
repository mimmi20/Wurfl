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
 * @package    WURFL_Admin
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');

$tw = new TeraWurfl();

$db = $tw->db;

$missing_tables = false;
if($db->connected === true){
	$required_tables = array(TeraWurflConfig::$TABLE_PREFIX.'Cache',TeraWurflConfig::$TABLE_PREFIX.'Index',TeraWurflConfig::$TABLE_PREFIX.'Merge');
	$tables = $db->getTableList();
// See what tables are in the DB
//die(var_export($tables,true));
	foreach($required_tables as $req_table){
		if(!in_array($req_table,$tables)){
			$missing_tables = true;
		}
	}
}


$mergestats = $db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Merge');
$indexstats = $db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Index');
$cachestats = $db->getTableStats(TeraWurflConfig::$TABLE_PREFIX.'Cache');
$matcherList = $db->getMatcherTableList();
$matchers = array();
foreach($matcherList as $name){
	$matchers[] = array('name'=>$name,'stats'=>$db->getTableStats($name));
}

$logfile = $tw->rootdir.TeraWurflConfig::$DATADIR.TeraWurflConfig::$LOG_FILE;

if(!is_readable($logfile) || filesize($logfile) < 5){
	$lastloglines = "Empty";
}else{
	$logarr = file($logfile);
	$loglines = 30;
	if(count($logarr)<$loglines)$loglines=count($logarr);
	$end = count($logarr)-1;
	$lastloglines = '';
	for($i=$end;$i>=($end-$loglines);$i--){
		$lastloglines .= @htmlspecialchars($logarr[$i])."<br />";
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Tera-WURFL Administration</title>
<link href="style.css" rel="stylesheet" type="text/css" /></head>

<body>
<table width="800">
	<tr><td>
<div align="center" class="titlediv">
	<p>		Tera-WURFL <?php echo $tw->release_version; ?> Administration<br />
		<span class="version">Loaded WURFL: <?php echo $tw->getSetting(TeraWurfl::$SETTING_WURFL_VERSION); ?></span></p>
</div>
</td></tr><tr><td>
		<h3><br />
			<a href="index.php">&lt;&lt; Back	to main page </a></h3>
		<table width="800" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th scope="col">Database Table </th>
			<th scope="col">Statistics</th>
		</tr>
		<tr>
			<td width="145" class="darkrow">MERGE<br />
					<span class="setting"><?php echo TeraWurflConfig::$TABLE_PREFIX.'Merge'?></span></td>
			<td width="655" class="darkrow">Rows: <span class="setting"><?php echo $mergestats['rows']?></span><br />
				Actual Devices: <span class="setting"><?php echo $mergestats['actual_devices']?></span> <br />
				Table Size: <span class="setting"><?php echo WurflSupport::formatBytes($mergestats['bytesize'])?></span><br />
				Purpose:<br />
				<span class="setting">The MERGE table holds all the data from the WURFL file.  When a new WURFL is loaded, it is loaded into this table first, then it is filtered through all the UserAgentMatchers and split into many different tables specific to each matching technique. This MERGE table is retained for a last chance lookup if the UserAgentMatchers and INDEX table are unable to provide a conclusive match.</span></td>
		</tr>
<?php if(!empty($indexstats)){ ?>
		<tr>
			<td class="lightrow">INDEX		<br />
				<span class="setting"><?php echo TeraWurflConfig::$TABLE_PREFIX.'Index'?></span></td>
		  <td class="lightrow">Rows: <span class="setting"><?php echo $indexstats['rows']?></span><br />
				Table Size: <span class="setting"><?php echo WurflSupport::formatBytes($indexstats['bytesize'])?></span><br />
				Purpose:<br />
				<span class="setting">The INDEX table acts as a lookup table for WURFL IDs and their respective UserAgentMatchers. </span></td>
		</tr>
<?php } ?>
		<tr>
			<td class="darkrow">CACHE		<br />
				<span class="setting"><?php echo TeraWurflConfig::$TABLE_PREFIX.'Cache'?></span></td>
<td class="darkrow">Rows: <span class="setting"><?php echo $cachestats['rows']?></span><br />
				Table Size: <span class="setting"><?php echo WurflSupport::formatBytes($cachestats['bytesize'])?></span><br />
				Purpose:<br />
				<span class="setting">The CACHE table stores unique user agents and the complete capabilities and device root that were determined when the device was first identified. <strong>Unlike version 1.x</strong>, the CACHE table stores every device that is detected <strong>permanently</strong>. When the device database is updated, the cached devices are also redetected and recached. This behavior is configurable.</span></td>
		</tr>
<?php if(!empty($matchers)){ ?>
		<tr>
			<td class="lightrow" style="vertical-align:top;">User Agent Matchers<br/>
				Purpose:<br />
				<span class="setting">The User Agent Matchers store similar user agents.  Tera-WURFL sorts all the devices into the most appropriate UserAgentMatcher table to make lookups faster and perform different matching hueristics on certain groups of devices.</span></td><td>
				<table>
<?php
$i=0;
foreach($matchers as $matcher){
	$class = ($i % 2 == 0)? "lightrow": "darkrow";
?>
<tr><td class="<?php echo $class;?>">UserAgentMatcher: <span class="setting"><?php echo $matcher['name']?></span><br />
Rows: <span class="setting"><?php echo $matcher['stats']['rows']?></span><br />
Table Size: <span class="setting"><?php echo WurflSupport::formatBytes($matcher['stats']['bytesize'])?></span></td></tr>
<?php
	$i++;
}
?></table></td>
		</tr>
<?php } ?>
	</table>
<p><br/>
			<br/>
	</p>
	<table width="800" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th scope="col">Tera-WURFL Settings</th>
		</tr>
		<tr><td>Installation Directory: <span class="setting"><?php echo dirname(dirname(__FILE__)); ?></span></td></tr>
		<tr>
			<td class="lightrow"><p>-- Database options --<br/>
				DB_HOST <span class="setting">
	<?php echo TeraWurflConfig::$DB_HOST?>
	</span>,	database server hostname or IP<br />
				DB_USER <span class="setting">
	<?php echo TeraWurflConfig::$DB_USER?>
	</span>,	database username (needs SELECT,INSERT,DELETE,DROP,CREATE)<br />
				DB_PASS <span class="setting">********</span>, database password<br />
				DB_SCHEMA <span class="setting">
	<?php echo TeraWurflConfig::$DB_SCHEMA?>
	</span>, database schema (database name)<br />
				DB_CONNECTOR <span class="setting">
	<?php echo TeraWurflConfig::$DB_CONNECTOR?>
	</span>, database type (MySQL4, MySQL5, MSSQL2005, etc...);<br />
				TABLE_PREFIX <span class="setting">
	<?php echo TeraWurflConfig::$TABLE_PREFIX?>
	</span>, prefix to be used for all table names<br />
							<br />
					-- General options --<br />
					WURFL_DL_URL <span class="setting">
						<?php echo TeraWurflConfig::$WURFL_DL_URL?>
							</span>, full URL to the current WURFL<br />
					DATADIR <span class="setting">
						<?php echo TeraWurflConfig::$DATADIR?>
			  </span>,	where all data is stored (wurfl.xml, temp files, logs)<br />
					  CACHE_ENABLE <span class="setting"><?php echo WurflSupport::showBool(TeraWurflConfig::$CACHE_ENABLE)?></span>, enables or disables the cache <br />
					PATCH_ENABLE <span class="setting">
						<?php echo WurflSupport::showBool(TeraWurflConfig::$PATCH_ENABLE)?>
				  </span>, enables or disables the patch<br />
					PATCH_FILE <span class="setting">
						<?php echo TeraWurflConfig::$PATCH_FILE?>
</span>, optional patch file for WURFL. To use more than one, separate them with semicolons<br />
					WURFL_FILE <span class="setting">
						<?php echo TeraWurflConfig::$WURFL_FILE?>
						</span>, path and filename of wurfl.xml<br />
					WURFL_LOG_FILE <span class="setting">
						<?php echo TeraWurflConfig::$LOG_FILE?>
						</span>, defines full path and filename for logging<br />
					LOG_LEVEL <span class="setting">
						<?php echo WurflSupport::showLogLevel(TeraWurflConfig::$LOG_LEVEL)?>
						</span>, desired logging level. Use the same constants as for PHP logging<br />
					OVERRIDE_MEMORY_LIMIT <span class="setting">
						<?php echo WurflSupport::showBool(TeraWurflConfig::$OVERRIDE_MEMORY_LIMIT)?>
						</span>, override PHP's default memory limit<br />
					MEMORY_LIMIT <span class="setting">
						<?php echo TeraWurflConfig::$MEMORY_LIMIT?>
						</span>, the amount of memory to allocate to PHP if OVERRIDE_MEMORY_LIMIT is enabled<br />
					SIMPLE_DESKTOP_ENGINE_ENABLE <span class="setting">
						<?php echo WurflSupport::showBool(TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE)?>
						</span>, enable the SimpleDesktop Detection Engine to increase performance<br />
					CAPABILITY_FILTER:
						<?php echo "<pre class=\"setting\">".var_export(TeraWurflConfig::$CAPABILITY_FILTER,true)."</pre>";?>
						the capability filter that is used to determine which capabilities are available<br />
			</p>
				</td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<table width="800" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th scope="col">Log File (last 30 lines) </th>
		</tr>
		<tr>
			<td class="lightrow"><div class="logfile"><?php echo $lastloglines?></div>
				<br/></td>
		</tr>
	</table>	<p>&nbsp; </p></td>
</tr></table>
</body>
</html>
