<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurfl
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
// Include the Tera-WURFL file
require_once realpath(dirname(__FILE__).'/TeraWurfl.php');
// Instantiate the Tera-WURFL object
$wurflObj = new TeraWurfl();
// Get the capabilities from the object
$wurflObj->GetDeviceCapabilitiesFromAgent(); //optionally pass the UA and HTTP_ACCEPT here
// Print the capabilities array
echo "<pre>".htmlspecialchars(var_export($wurflObj->capabilities,true))."</pre>";
