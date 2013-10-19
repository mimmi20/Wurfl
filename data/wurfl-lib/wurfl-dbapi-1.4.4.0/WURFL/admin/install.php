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

// Enable verbose error reporting to show major errors during file inclusion
ini_set('display_errors','on');
error_reporting(E_ALL);

if(!class_exists('TeraWurflConfig',false)){
        if(!file_exists(dirname(__FILE__).'/../TeraWurflConfig.php')){
                die("The file TeraWurflConfig.php does not exist.  Please copy TeraWurflConfig.php.example to TeraWurflConfig.php and modify the configuration with your database details.");
        }
        if(!realpath(dirname(__FILE__).'/../TeraWurflConfig.php')){
                die("Although TeraWurflConfig.php exists, it is not accessible by realpath().  This is likely due to permissions.  Please make sure all directories have the execute permission.");
        }
}

require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');

$dbconnector = 'TeraWurflDatabase_'.TeraWurflConfig::$DB_CONNECTOR;
if (!call_user_func(array($dbconnector, 'extensionLoaded'))) {
	die("You do not have the PHP extensions required to use the database connector $dbconnector.  If you are using the default MySQL5 connector, you need the PHP 'mysqli' extension loaded");
}

// Turn error reporting down now that the suporting files have been loaded
error_reporting(E_ERROR);
@$tw = new TeraWurfl();

$dir = $tw->rootdir.TeraWurflConfig::$DATADIR;
$logfile = TeraWurflConfig::$LOG_FILE;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Tera-WURFL Installation</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table width="800">
	<tr><td>
<div align="center" class="titlediv">
	<p>		Tera-WURFL <?php echo $tw->release_version; ?> Administration<br />
		</p>
</div>
</td></tr><tr><td>
	<p>&nbsp;</p>
	<table width="800" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th colspan="2" scope="col">Checking Installation </th>
		</tr>
		<tr>
			<td colspan="2" valign="bottom" class="darkrow"><p><strong><u>PHP Configuration</u></strong>
<br />
</p>			</td>
		</tr>
		<tr>
			<td width="17" class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td width="783" class="lightrow"><strong>PHP Version</strong>:
<?php
if(version_compare(PHP_VERSION,TeraWurfl::$required_php_version) === 1){
	echo PHP_VERSION."... OK";
}else{
	echo "<span class=\"error\">ERROR:</span> PHP ".TeraWurfl::$required_php_version." is required but you have ".PHP_VERSION;
}
?></td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>ZipArchive Support</strong>			  <?php
if(class_exists("ZipArchive",false)){
	echo "... OK";
}else{
	echo "... <span class=\"error\">WARNING</span><br/> In order to update the WURFL File from the Internet, you must have support for the <strong>ZipArchive module</strong>.
	This module is included with PHP since 5.2.0.  You can get the ZipArchive class from the <a href=\"http://pecl.php.net/package/zip\" target=\"_blank\">PECL Zip package</a>.
	Note: you can still use Tera-WURFL without ZipArchive, Tera-WURFL will attempt to call the gunzip program from your system to unzip the compressed WURFL archive.
	If this fails, you must download the archive manually and extract wurfl.xml to your data/ directory.";
}
?></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><strong>MySQLi Support</strong>			  <?php
if(class_exists("MySQLi")){ // SQL Driver for PHP: function_exists("sqlsrv_connect")
	echo "... OK";
}else{
	echo "... <span class=\"error\">WARNING</span><br/>Since version 2.0, Tera-WURFL requires the <strong>MySQLi</strong> module to use MySQL.  More information about obtaining MySQLi is available at <a href=\"http://www.php.net/manual/en/book.mysqli.php\" target=\"_blank\">www.php.net</a>.<br/><br/>
	
	If you are using the <i>EXPERIMENTAL</i> Microsoft SQL Server DatabaseConnector you can ignore this error message, however, you will need the <a href=\"http://msdn.microsoft.com/en-us/library/ms131321.aspx\" target=\"_blank\">SQL Server Native Client</a> and <a href=\"http://www.microsoft.com/downloads/details.aspx?displaylang=en&FamilyID=ccdf728b-1ea0-48a8-a84a-5052214caad9\" target=\"_blank\">SQL Server Driver for PHP 1.1</a> installed.";
}
?></td>
		</tr>
        <tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>PHP Memory Limit</strong>:
<?php
if(TeraWurflConfig::$OVERRIDE_MEMORY_LIMIT){
	echo TeraWurflConfig::$MEMORY_LIMIT." (via TeraWurflConfig::\$MEMORY_LIMIT)";
}else{
	echo ini_get("memory_limit")." (via php.ini)";
}
?><br/><strong>When you update the WURFL, PHP may consume over 200MB of RAM while parsing, sorting and indexing the data.</strong></td>
		</tr>
		<tr>
			<td colspan="2" valign="bottom" class="darkrow"><p><strong><u>File Permissions</u></strong>
<br />
</p>			</td>
</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>WURFL File</strong>: <?php echo $dir.TeraWurflConfig::$WURFL_FILE?>...
				<?php
if(is_file($dir.TeraWurflConfig::$WURFL_FILE) && is_readable($dir.TeraWurflConfig::$WURFL_FILE))
echo "OK";
else
echo "<span class=\"error\">WARNING:</span> File doesn't exist or isn't readable.  You can continue like this, you just can't update the database from your local wurfl.xml.";
?></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><strong>PATCH Files</strong>:<br/>
				<?php
$files = explode(';',TeraWurflConfig::$PATCH_FILE);
foreach($files as $thisfile){
	echo "$dir$thisfile...";
	if(is_file($dir.$thisfile) && is_readable($dir.$thisfile))
		echo "OK<br/>";
	else
		echo "<span class=\"error\">WARNING:</span> File doesn't exist or isn't readable.  You may ignore this error if patching is disabled.<br/>";
}
?></td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>DATA Directory</strong>: <?php echo $dir?>...
				<?php
if(is_dir($dir) && is_readable($dir) && is_writable($dir))
echo "OK";
else
echo "<span class=\"error\">ERROR:</span> Directory doesn't exist or isn't writeable.  This directory should be owned by the webserver user or chmoded to 777 for the log file and the online updates to work. </br>Here's the best way to do it in Ubuntu:<br/><pre>sudo chgrp -R www-data data/
sudo chmod -R g+rw data/</pre>";
?></td>
		</tr>
		<tr>
<td colspan="2" valign="bottom" class="darkrow"><p><strong><u>Database Settings</u></strong></p></td>
		</tr>
		<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><strong>Host</strong>: <?php echo TeraWurflConfig::$DB_HOST?></td>
		</tr>
		<tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>Username</strong>: <?php echo TeraWurflConfig::$DB_USER?></td>
		</tr>
		<tr>
			<td class="lightrow"><span class="lightrow"><img src="triangle.gif" width="10" height="11" /></span></td>
			<td class="lightrow"><strong>Connecting to DB server</strong>...
<?php
if(!class_exists('MySQLi',false)){
	echo "<span class=\"error\">ERROR:</span> MySQLi is not installed or enabled, see errors above.";
}elseif(function_exists('mysqli_connect_errno') && mysqli_connect_errno()){
	echo "<span class=\"error\">ERROR:</span> ".mysqli_connect_error();
}else{
	echo "OK";
}
$errors = $tw->db->verifyConfig();
if(!empty($errors)){
	foreach($errors as $error){
		echo "<br/><span class=\"error\">ERROR:</span> $error";
	}
}
?></td>
		</tr>
        <tr>
			<td class="darkrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="darkrow"><strong>DB Server Version</strong>: 
<?php
if(!$tw->db->connected){
	echo "<span class=\"error\">ERROR:</span> The version number cannot be determined because you are not connected.";
}else{
	$mysql_raw_version = $tw->db->getServerVersion();
	if($mysql_raw_version !== false){
		$mysql_version = (int)$mysql_raw_version[0];
		echo $mysql_raw_version."<br/>";
		if(TeraWurflConfig::$DB_CONNECTOR == "MySQL4" && $mysql_version > 4){
			echo "<span class=\"error\">NOTICE:</span> You are using the MySQL4 connector with MySQL5.  Although this will work, the MySQL5 connector provides much better performance. 
			Since the MySQL4 connector does not use Stored Procedures, you can also use it if you want to use MySQL5 without Stored Procedures.";
		}elseif(TeraWurflConfig::$DB_CONNECTOR == "MySQL5" && $mysql_version < 5){
			echo "<span class=\"error\">ERROR:</span> You are using the MySQL5 connector with MySQL4.  Please change the DatabaseConnector to MySQL4.";
		}
	}
}
?>
			</td>
		</tr>
       	<tr>
			<td class="lightrow"><img src="triangle.gif" width="10" height="11" /></td>
			<td class="lightrow"><strong>DB Name</strong> (schema): <?php echo TeraWurflConfig::$DB_SCHEMA?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div align="left" style="padding-left:15px;"><br />
				 If everything looks ok, <strong>delete <?php echo $tw->rootdir; ?>admin/install.php</strong>, then update your database from one of the following sources:
				 <ul><li><a href="updatedb.php?source=local">Your local WURFL file</a> (<?php echo TeraWurflConfig::$WURFL_FILE?>)</li>
				 <li><a href="updatedb.php?source=remote&force=true">The current released WURFL</a> (<?php echo TeraWurflConfig::$WURFL_DL_URL?>)</li>
				 </ul>
			</div></td>
		</tr>
	</table>
	</td>
</tr></table>
</body>
</html>
