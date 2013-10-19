<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Remote Tera-WURFL Remote Client Example</title>
</head>
<body>
<?php
$start = microtime(true);
require_once realpath(dirname(__FILE__).'/../../TeraWurflRemoteClient.php');
// NOTE: You must use $FORMAT_XML to communicate with Tera-WURFL 2.1.1 and earlier!
$data_format = TeraWurflRemoteClient::$FORMAT_JSON;
$wurflObj = new TeraWurflRemoteClient('http://localhost/Tera-Wurfl/webservice.php',$data_format);
$capabilities = array("is_wireless_device","model_name", "brand_name", "ux_full_desktop", "fake_capability");
$ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; HTC; Windows Phone 8X by HTC)';
$wurflObj->getCapabilitiesFromAgent($ua,$capabilities);
$time = round(microtime(true)-$start,6);
echo "<h3>Response from WURFL API ".$wurflObj->getAPIVersion()."</h3>";
echo "<pre>".var_export($wurflObj->capabilities,true)."</pre>";
if($wurflObj->errors){
	echo "<h3>Errors</h3>";
	echo "<pre>".var_export($wurflObj->errors,true)."</pre>";
}
echo "<hr/>Total Time: $time";
?>
</body>
</html>