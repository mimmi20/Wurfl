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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
// Include the Tera-WURFL file
require_once realpath(dirname(__FILE__).'/TeraWurfl.php');
// Instantiate the Tera-WURFL object
$wurflObj = new TeraWurfl();
// Get the capabilities from the object
$wurflObj->getDeviceCapabilitiesFromRequest();
// Print the capabilities array
echo "<pre>".htmlspecialchars(var_export($wurflObj->capabilities,true))."</pre>";
