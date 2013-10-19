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
require_once realpath(dirname(__FILE__).'/../../TeraWurfl.php');

$tw = new TeraWurfl();
$db = $tw->db;

$missing_tables = false;
if($db->connected === true){
	$required_tables = array(TeraWurflConfig::$TABLE_PREFIX.'Cache');
	$tables = $db->getTableList();
// See what tables are in the DB
//die(var_export($tables,true));
	foreach($required_tables as $req_table){
		if(!in_array($req_table,$tables)){
			$missing_tables = true;
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Tera-WURFL Cache Browser</title>
<link href="../style.css" rel="stylesheet" type="text/css" /></head>
<body>
<table width="800">
	<tr><td>
<div align="center" class="titlediv">
	<p>		Tera-WURFL Cache Browser<br />
		<span class="version">Version <?php echo $tw->release_branch." ".$tw->release_version; ?></span></p>
</div>
</td></tr><tr><td>
		<h3><br />
			<a href="../index.php">&lt;&lt; Back to main page </a></h3>
<table>
<tr><th colspan="2">Cached User Agents</th></tr>
<?php
$cached_uas = $db->getCachedUserAgents();
$i = 0;
foreach($cached_uas as $ua){
	$class = ($i++ % 2 == 0)? 'lightrow': 'darkrow';
	echo "<tr><td>$i)</td><td class=\"$class\"><pre style=\"padding: 0px; margin: 0px;\"><a style=\"text-decoration: none;\" target=\"_blank\" href=\"show_capabilities.php?ua=".urlencode($ua)."\" title=\"Click to see details\">".htmlspecialchars($ua)."</a></pre></td></tr>";
}
?>
</table>
				<br/></td>
		</tr>
	</table>
</body>
</html>
