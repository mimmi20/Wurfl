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
require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');

try {
	$tw = new TeraWurfl();
}catch(Exception $e){
	
}

$patch_changed = false;
$custom_patch = $tw->rootdir.TeraWurflConfig::$DATADIR."custom_web_patch.xml";
$custom_patch_user_agents = $tw->rootdir.TeraWurflConfig::$DATADIR."custom_web_patch_uas.txt";

if(isset($_POST['action']) && $_POST['action']=='generate_patch'){
	$patch_data = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<wurfl_patch>\n\t<devices>";
	$i = 0;
	$rawdata = $_POST['data'];
	if(get_magic_quotes_gpc()){$rawdata=stripslashes($rawdata);}
	$rawdata = preg_replace('/[\r\n]+/',"\n",$rawdata);
	$data = explode("\n",$rawdata);
	foreach($data as $line){
		$line = trim($line);
		if($line == "")continue;
		$patch_data .= "\n\t\t".'<device user_agent="'.htmlspecialchars($line).'" fall_back="generic_web_browser" id="terawurfl_generic_web_browser'.$i++.'"/>';
	}
	$patch_data .= "\n\t</devices>\n</wurfl_patch>";
	file_put_contents($custom_patch_user_agents,$rawdata);
	file_put_contents($custom_patch,$patch_data);
	$patch_changed = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Tera-WURFL Custom Patch Generator</title>
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<table width="100%">
	<tr><td>
	<div align="center" class="titlediv">
	<p>Tera-WURFL Custom Patch Generator<br/>
		<span class="version">Version <?php echo $tw->release_branch." ".$tw->release_version; ?></span></p>
	</div>
	<?php if($patch_changed){?><div align="center" class="noticediv" style="width: 100%">Custom patch file saved.  <a href="#patch">View patch file</a></div><?php }?>
	</td></tr>
	<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr><th>Enter your non-mobile user agents below</th></tr>
	</table>
	</td></tr>
	<tr><td class="lightrow">Enter your non-mobile user agents below, one per line, and press <strong>Generate Patch File</strong>.  These user agents will be compiled into the Tera-WURFL custom patch file <strong><?php echo $custom_patch; ?></strong>. After you submit the changes, go to the <a href="index.php">Tera-WURFL Administration Page</a> and update your WURFL database to load the new patch file.</td></tr>
	<tr>
		<td><form action="generatePatch.php" method="post">
		<input type="hidden" name="action" value="generate_patch" />
		<textarea name="data" rows="25" cols="97" style="width: 100%;"><?php echo file_get_contents($custom_patch_user_agents);?></textarea>
		<br/><center><input type="submit" value="Generate Patch File" name="submit" /></center>
		</form></td>
	</tr>
</table>
<pre><a name="patch"></a><?php if($patch_changed){echo htmlspecialchars(file_get_contents($custom_patch));}?></pre>
</body>
</html>