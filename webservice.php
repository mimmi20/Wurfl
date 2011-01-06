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
/*
 * webservice.php provides a method of querying a remote Tera-WURFL for device capabilities.
 * This file requires Tera-WURFL >= 2.1.1
 * 
 * Parameters:
 * 	ua: The user agent you want to lookup (url encoded/escaped)
 *  search: The capabilities or groups you are looking for (delimited by '|')
 *  format: (optional) The data format to return the result in: xml or json.  xml is default
 * 
 * Usage Example:
 * webservice.php?ua=SonyEricssonK700i/R2AC%20SEMC-Browser/4.0.2%20Profile/MIDP-2.0%20Configuration/CLDC-1.1&search=brand_name|model_name|uaprof|fakecapa|image_format|fakegroup
 * 
 * Returns:
 * XML data with the results, in the following format:
<?xml version="1.0" encoding="iso-8859-1"?>
<TeraWURFLQuery>
	<device apiVersion="2.1.3" mtime="1276096668" useragent="SonyEricssonK700i/R2AY SEMC-Browser/4.0.3 Profile/MIDP-2.0 Configuration/CLDC-1.1" id="sonyericsson_k700i_ver1subr2ay">
		<capability name="brand_name" value="SonyEricsson"/>
		<capability name="model_name" value="K700i"/>
		<capability name="uaprof" value="http://wap.sonyericsson.com/UAprof/K700iR101.xml"/>
		<capability name="fakecapa" value=""/>
		<capability name="greyscale" value="false"/>
		<capability name="jpg" value="true"/>
		<capability name="gif" value="true"/>
		<capability name="transparent_png_index" value="false"/>
		<capability name="epoc_bmp" value="false"/>
		<capability name="bmp" value="true"/>
		<capability name="wbmp" value="true"/>
		<capability name="gif_animated" value="true"/>
		<capability name="colors" value="65536"/>
		<capability name="svgt_1_1_plus" value="false"/>
		<capability name="svgt_1_1" value="true"/>
		<capability name="transparent_png_alpha" value="false"/>
		<capability name="png" value="true"/>
		<capability name="tiff" value="false"/>
		<capability name="fakegroup" value=""/>
	</device>
	<errors>
		<error name="fakecapa" description="The group or capability is not valid."/>
		<error name="fakegroup" description="The group or capability is not valid."/>
	</errors>
</TeraWURFLQuery>
 * 
 * You can specify the following options via GET or POST:
 * ua:		User Agent (required)
 * search:	Returns the specified capabilities (e.g. mp3) and groups (e.g. product_info)
 * 			Multiple capabilities and groups can be separated by '|'.
 * 
 * This script is designed to always return valid XML data so your client doesn't
 * break.  If your query generated errors, they will be in:
 * 		<TeraWURFLQuery>
 * 			<errors>
 * Error elements have two properties:
 * 		name: The name of the capability or group in error
 * 		desc: The description of the error
 * See the example above.  If you searched for an invalid capability or group, it
 * will still be included in the XML data structure, but it will be NULL.
 * 
 * To see the nicely formatted XML output in your browser, you can do this:
 * <code>
 * echo "<pre>".htmlspecialchars(var_export($webservice->getXMLResponse(),true))."</pre>";
 * </code>
 */
require_once realpath(dirname(__FILE__).'/./TeraWurflWebservice.php');
$userAgent = array_key_exists('ua',$_REQUEST)? $_REQUEST['ua']: null;
$searchPhrase = array_key_exists('search',$_REQUEST)? $_REQUEST['search']: null;
$data_format = (array_key_exists('format',$_REQUEST) && $_REQUEST['format'])? $_REQUEST['format']: null;
$webservice = new TeraWurflWebservice($userAgent,$searchPhrase,$data_format);
$webservice->sendResponse();