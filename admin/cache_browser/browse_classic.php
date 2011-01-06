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
