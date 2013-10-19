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
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflUtils/TeraWurflUpdater.php');

@ini_set("display_errors","on");
error_reporting(E_ALL);
$update_source = isset($_GET['source'])? $_GET['source']: 'local';

$wurfl = new TeraWurfl();
$updater = new TeraWurflUpdater($wurfl, $update_source);


if (isset($_GET['action']) && $_GET['action']=='rebuildCache') {
	$wurfl->db->rebuildCacheTable();
	header("Location: index.php?msg=".urlencode("The cache has been successfully rebuilt ({$wurfl->db->numQueries} queries).")."&severity=notice");
	exit(0);
}
if (isset($_GET['action']) && $_GET['action']=='clearCache') {
	$wurfl->db->createCacheTable();
	header("Location: index.php?msg=".urlencode("The cache has been successfully cleared ({$wurfl->db->numQueries} queries).")."&severity=notice");
	exit(0);
}

$force_update = isset($_GET['force']);
if ($update_source == 'remote') {
	try {
		$available = $updater->isUpdateAvailable();
	} catch(Exception $e) {
		//echo "Unable to check if update is available, assuming it is.\n";
		$available = true;
	}
	if (!$force_update && !$available) {
		header("Location: index.php?msg=".urlencode("Your WURFL data is already up to date. <a href=\"updatedb.php?source=remote&force=true\">Force update</a>")."&severity=notice");
		exit(0);
	}
}

try {
	$status = $updater->update();
} catch (TeraWurflUpdateDownloaderException $e) {
	$sf = ($updater->downloader->download_url == 'http://downloads.sourceforge.net/project/wurfl/WURFL/latest/wurfl-latest.zip');
	header("Location: index.php?msg=".urlencode($e->getMessage())."&severity=error&sf404=".$sf);
	exit(0);
}

if ($status) {
	echo "<strong>Database Update OK</strong><hr />";
	echo "Total Time: ".$updater->loader->totalLoadTime()."<br/>";
	echo "Parse Time: ".$updater->loader->parseTime()." (".$updater->loader->getParserName().")<br/>";
	echo "Validate Time: ".$updater->loader->validateTime()."<br/>";
	echo "Sort Time: ".$updater->loader->sortTime()."<br/>";
	echo "Patch Time: ".$updater->loader->patchTime()."<br/>";
	echo "Database Time: ".$updater->loader->databaseTime()."<br/>";
	echo "Cache Rebuild Time: ".$updater->loader->cacheRebuildTime()."<br/>";
	echo "Number of Queries: ".$wurfl->db->numQueries."<br/>";
	if(version_compare(PHP_VERSION,'5.2.0') === 1){
		echo "PHP Memory Usage: ".WurflSupport::formatBytes(memory_get_usage())."<br/>";
	}
	echo "--------------------------------<br/>";
	echo "WURFL Version: ".$updater->loader->version." (".$updater->loader->last_updated.")<br />";
	echo "WURFL Devices: ".$updater->loader->mainDevices."<br/>";
	echo "PATCH New Devices: ".$updater->loader->patchAddedDevices."<br/>";
	echo "PATCH Merged Devices: ".$updater->loader->patchMergedDevices."<br/>";
	if(count($updater->loader->errors) > 0){
		echo "<pre>";
		foreach($updater->loader->errors as $error) echo htmlspecialchars($error)."\n";
		echo "</pre>";
	}
}else{
	echo "ERROR LOADING DATA!<br/>";
	echo "Errors: <br/>\n";
	echo "<pre>";
	foreach($updater->loader->errors as $error) echo htmlspecialchars($error)."\n";
	echo "</pre>";
}

echo "<hr/><a href=\"index.php\">Return to administration tool.</a>";

