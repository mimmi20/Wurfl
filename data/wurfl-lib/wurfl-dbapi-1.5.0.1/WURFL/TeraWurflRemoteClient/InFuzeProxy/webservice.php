<?php
/**
 * Copyright (c) 2013 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/*
 * webservice.php provides a method of querying a remote Tera-WURFL for device capabilities.
 * This file requires WURFL InFuze
 * 
 * Parameters:
 * 	ua: The user agent you want to lookup (url encoded/escaped)
 *  search: The capabilities or groups you are looking for (delimited by '|')
 *  format: (optional) The data format to return the result in: xml or json.  xml is default
 * 
 * Usage Example:
 * webservice.php?search=brand_name|model_name|uaprof|fakecapa|image_format|fakegroup
 * 
 */
require_once dirname(__FILE__).'/TeraWurflWebservice.php';
require_once dirname(__FILE__).'/TeraWurflInFuzeProxy.php';

// InFuze with environment vars (ex: NGINX, Apache)
$infuze_provider = new InFuzeProvider_Environment('WURFL_');

// InFuze with HTTP Headers (ex: Varnish-Cache, NGINX, Apache)
// $infuze_provider = new InFuzeProvider_HttpHeaders('x-wurfl-');

$user_agent = array_key_exists('ua',$_REQUEST)? $_REQUEST['ua']: null;
$search_phrase = array_key_exists('search',$_REQUEST)? $_REQUEST['search']: null;
$data_format = (array_key_exists('format',$_REQUEST) && $_REQUEST['format'])? $_REQUEST['format']: null;

$webservice = new TeraWurflInFuzeProxy($user_agent, $search_phrase, $data_format, $infuze_provider);
$webservice->sendResponse();