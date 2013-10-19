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

try {
	$tw = new TeraWurfl();
}catch(Exception $e){
	
}

$db = $tw->db;
$wurflfile = $tw->rootdir.TeraWurflConfig::$DATADIR.TeraWurflConfig::$WURFL_FILE;

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
}else{
	header("Location: install.php");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>WURFL Administration</title>
<link href="style.css" rel="stylesheet" type="text/css" /></head>

<body>
<table width="800">
	<tr><td>
<div align="center" class="titlediv">
	<p>WURFL DB API <?php echo $tw->release_version; ?> Administration<br />
		<span class="version">ScientiaMobile WURFL Database API <?php echo $tw->release_version; ?> (aka Tera-WURFL <?php echo $tw->historical_release_version; ?>)<br/>Loaded WURFL: <?php echo $tw->getSetting(TeraWurfl::$SETTING_WURFL_VERSION); ?></span></p>
</div>
<?php
if(isset($_GET['msg']) && $_GET['severity']){
	$severity = ($_GET['severity']=='notice')? 'noticediv': 'errordiv';
?>
	<div class="<?php echo $severity; ?>">
	<?php
	echo $_GET['msg']; 
	if (isset($_GET['sf404']) && $_GET['sf404'] == '1') {
		?>
		<br/><br/>
		<strong>Important:</strong> The license of the WURFL Data has recently changed. ScientiaMobile has temporarily moved the WURFL download location to ensure that you 
		are aware of the changes.  Please visit <a href="http://sourceforge.net/projects/wurfl/files/WURFL/" target="_blank">the WURFL Sourceforge page</a>, download the desired wurfl.xml and place it in the data/ directory, then run the 
		update from local instead of remote.
		<?php
	}
	?>
	</div>
<?php
}
?>
</td></tr><tr><td>
	<p>&nbsp;		</p>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="2" scope="col">Administration</th>
		</tr>
		<tr>
			<td width="16" class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td width="744" class="lightrow"><a href="updatedb.php?source=local">Update database from local file<br />
			</a><strong>Location</strong>: <?php echo $wurflfile; ?><br />
			Updates your WURFL database from a local file. The location of this file is defined in <strong>TeraWurflConfig.php</strong>.</td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><a href="updatedb.php?source=remote">Update database from a remote repository</a><br />
			<strong>Location</strong>: <?php echo TeraWurflConfig::$WURFL_DL_URL; ?><br />			Updates your WURFL database with the <strong>current stable release</strong> from the <a href="http://sourceforge.net/projects/wurfl/files/WURFL/">official WURFL download site</a>.<br />
			<span class="error"><strong>WARNING: </strong>This will replace your existing wurfl.xml</span><br/></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><a href="updatedb.php?action=rebuildCache">Rebuild the device cache</a><br/>Rebuilds the cache table by running through all the devices in the existing cache table and redetecting them using the current WURFL data and re-caching them. This is automatically done when you update the WURFL, but you can manually rebuild it with this link.</td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><a href="updatedb.php?action=clearCache">Clear the device cache</a><br/>Clears (truncates) the device cache.<br/><span class="error"><strong>WARNING:</strong> This will DELETE the device cache, so all devices will need to be redetected.</span></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><a href="generatePatch.php">Generate Patch File</a><br/>Allows you to add non-mobile user agents to the the custom patch file from the web interface.  Once you save the changes you can return to this page and reload the WURFL database to apply your changes.  All the user agents you add will be given a fallback id of <strong>generic_web_browser</strong> so as to be detected as a non-mobile device.</td>
		</tr>
	</table>
	<br/>
	<br/>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="2" scope="col">Diagnostics</th>
		</tr>
		<tr>
			<td width="16" class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td width="744" class="darkrow"><a href="../check_wurfl.php">Tera-WURFL test script</a><br />
				This is	
			very similar to the <strong>check_wurfl.php</strong> script included with the <a href="http://wurfl.sourceforge.net/php/">PHP Tools</a> package. This is a good way to test your installation of Tera-WURFL and see how the class handles different user agents.</td>
		</tr>
		<tr>
			<td width="16" class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td width="744" class="lightrow"><a href="cache_browser/browse_classic.php">Browse the device cache</a><br />
			Displays the contents of your cache and allows you to see the entire capabilities listing for each device as it appears in the cache.</td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><a href="stats.php">Statistics, Settings, Log File </a><br />
				See statistics about your database tables with detailed descriptions,your current settings and the last errors in your log file.</td>
		</tr>
	</table>
	<br/><br/><img style="display:none" src="http://www.tera-wurfl.com/twnews.php?a=<?php echo urlencode(serialize(array($_SERVER['SERVER_SOFTWARE'],$_SERVER['SERVER_NAME'],$_SERVER['SERVER_ADDR'],$tw->release_branch,$tw->release_version)));?>" width="1" height="1"/>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="2" scope="col">Support</th>
		</tr>
        <tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><form action="http://www.tera-wurfl.com/ua_submission/" method="get" target="_blank"><p>Having a problem with a particular user agent? Make sure you're using the latest WURFL by updating the database above. If it still isn't being detected properly, you may submit it to the Tera-WURFL development team here:</p>
			  <p>Paste the User Agent here:<br />
                <input type="hidden" name="version" id="version" value="<?php echo $tw->release_version; ?>" />
			    <input type="text" name="ua" id="ua" style="width:95%" />
			  </p>
			  <p>
			    <input type="submit" name="submit" id="submit" value="Submit User Agent" />
              </p>
			</form></td>
		</tr>
		<tr>
			<td width="16" class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td width="744" class="darkrow"><a href="http://dbapi.scientiamobile.com/explore/">ScientiaMobile WURFL Explorer - explore the WURFL!</a></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><a href="http://www.scientiamobile.com/">ScientiaMobile - Commercial WURFL APIs and Support</a></td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><a href="http://dbapi.scientiamobile.com/wiki/index.php/Installation">Online Documentation</a></td>
		</tr>        
	</table>
	<br /><br />
	</td>
</tr>
</table>
</body>
</html>
