# ScientiaMobile InFuze Proxy #

## About ##
The WURFL InFuze Proxy allows developers to use the existing `TeraWurflRemoteClient` class that ships with the ScientiaMobile Database API (aka Tera-WURFL) to access a high-performance WURFL InFuze server.

## Requirements ##

### Client Software ###
The `TeraWurflRemoteClient` class must be version 3.0+ or the responses will be incorrect.  The version number can be found in the `TeraWurflRemoteClient.php` file itself.

	// This version is too old to be used with InFuze
	protected $clientVersion = '2.1.4';

	// You will need the client with version 3.0+
	protected $clientVersion = '3.0.0';

## Installation / Configuration ##

### Frontend Server / Client Code ###
With the standard client, you could request both individual capabilities (ex: `is_tablet`, `model_name`) and capability groups (ex: `product_info`, `display`).  With the InFuze Proxy, only individual capabilities are supported, **capability groups are not supported**.

If you are using capability groups now, you will need to replace them with the actual capabilities you want:

	// Old code using capability group 'product_info'
	$wurflObj = new TeraWurflRemoteClient('http://127.0.0.1/webservice.php', $data_format);
	$capabilities = array("product_info");
	$wurflObj->getDeviceCapabilitiesFromAgent(null, $capabilities);

	// New code using the individual capabilities
	$wurflObj = new TeraWurflRemoteClient('http://127.0.0.1/webservice.php', $data_format);
	$capabilities = array("is_wireless_device","model_name", "brand_name", "ux_full_desktop");
	$wurflObj->getDeviceCapabilitiesFromAgent(null, $capabilities);
	
	// Alternately, you can omit the $capabilities to get all the InFuze capabilities
	$wurflObj = new TeraWurflRemoteClient('http://127.0.0.1/webservice.php', $data_format);
	$wurflObj->getDeviceCapabilitiesFromAgent();

### InFuze Server ###
WURFL InFuze for Apache, NGINX and Varnish-Cache are typically configured to either expose WURFL capabilities to the webserver's environment, or to add the WURFL capabilities as HTTP headers into the incoming HTTP request before it is sent to your application.  The InFuze proxy reads this information and sends it back in a format that the `TeraWurflRemoteClient` can consume.  In order for this to work, you will need to copy this proxy script to your webserver where the InFuze module has already added the WURFL data to the environment or HTTP headers.

#### Installation ####
Copy the files from the InFuzeProxy directory to a location on your webserver.  You will need to specify the URL to the included `webservice.php` file when you use the `TeraWurflRemoteClient`, so it must be accessible via HTTP.

	InFuzeProxy/
	  TeraWurflInFuzeProxy.php
	  TeraWurflWebservice.php
	  webservice.php

#### Configuration ####
Edit `webservice.php` - this is the file that will serve the requests from `TeraWurflRemoteClient`.

There are a couple things you may need to configure:

	// InFuze with environment vars (ex: NGINX, Apache)
	$infuze_provider = new InFuzeProvider_Environment('WURFL_');
	
	// InFuze with HTTP Headers (ex: Varnish-Cache, NGINX, Apache)
	// $infuze_provider = new InFuzeProvider_HttpHeaders('x-wurfl-');

First, you will need to use the InFuzeProvider that matches your InFuze configuration, so `InFuzeProvider_Environment` for environment variables or `InFuzeProvider_HttpHeaders` for HTTP headers.

The argument that is passed to the constructor is the string that the WURFL capabilities are prefixed with in your environment.  The defaults should be correct in most cases.

## Testing ##
To test that everything is working properly, you can use this PHP example (simplified from `TeraWurflRemoteClient/examples/php/example.php`)

	<html>
	<head><title>Remote Tera-WURFL Remote Client Example</title></head>
	<body>
	<?php
	// Make sure TeraWurflRemoteClient.php is in the same folder
	require_once dirname(__FILE__).'/TeraWurflRemoteClient.php';
	$data_format = TeraWurflRemoteClient::$FORMAT_JSON;
	$wurflObj = new TeraWurflRemoteClient('http://localhost/webservice.php', $data_format);
	$capabilities = array("is_wireless_device","model_name", "brand_name", "ux_full_desktop", "fake_capability");
	$ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; HTC; Windows Phone 8X by HTC)';
	$wurflObj->getCapabilitiesFromAgent($ua, $capabilities);
	echo "<h3>Response from WURFL API ".$wurflObj->getAPIVersion()."</h3>";
	echo "<pre>".var_export($wurflObj->capabilities,true)."</pre>";
	if($wurflObj->errors){
		echo "<h3>Errors</h3>";
		echo "<pre>".var_export($wurflObj->errors,true)."</pre>";
	}
	?>
	</body>
	</html> 

Adjsut the URL to point to the `webservice.php` file that came with the InFuze Proxy

	$wurflObj = new TeraWurflRemoteClient('http://my-infuze-server/InFuzeProxy/webservice.php', $data_format);

Now, visit the URL to the example you created, and if everything is working you should see something like this:

	Response from WURFL API InFuze
	
	array (
	  'id' => 'htc_8x_ver1',
	  'is_wireless_device' => true,
	  'model_name' => 'Windows Phone 8X',
	  'brand_name' => 'HTC',
	  'ux_full_desktop' => false,
	  'fake_capability' => NULL,
	)
	Errors
	
	array (
	  'fake_capability' => 'The group or capability is not valid.',
	)

If you see a different `id` like `google_chrome` or `generic_web_browser`, please make sure you are using `TeraWurflRemoteClient` version 3.0 or higher, as stated in the Requirements section.