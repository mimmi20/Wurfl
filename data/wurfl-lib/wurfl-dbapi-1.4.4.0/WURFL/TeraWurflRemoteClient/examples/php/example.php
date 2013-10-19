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
$capabilities = array("product_info","fake_capability");
$wurflObj->getCapabilitiesFromAgent(null,$capabilities);
$time = round(microtime(true)-$start,6);
echo "<h3>Response from Tera-WURFL ".$wurflObj->getAPIVersion()."</h3>";
echo "<pre>".var_export($wurflObj->capabilities,true)."</pre>";
if($wurflObj->errors){
	foreach($wurflObj->errors as $name => $error){
		echo "$name: $error<br/>";
	}
}
echo "<hr/>Total Time: $time";
?>
</body>
</html>