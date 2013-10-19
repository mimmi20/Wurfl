<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @package    WURFL_Database
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */

btnDetect.addEventListener(MouseEvent.CLICK,startDetection);
function startDetection(event:Event):void {
	var xml:XML;
	 
	var urlRequest:URLRequest = new URLRequest("http://localhost/Tera-Wurfl/webservice.php?ua=" + escape(txtUA.text) + "&search=" + txtCapabilities.text);
	 
	var urlLoader:URLLoader = new URLLoader();
	urlLoader.addEventListener(Event.COMPLETE, urlLoader_complete);
	urlLoader.load(urlRequest);
}
function urlLoader_complete(evt:Event):void {
	txtResult.text = 'Result:\n';
    var xml = new XML(evt.currentTarget.data);
	for each( var i:Object in xml..capability){
		txtResult.appendText(i.@name + ": " + i.@value + "\n");
	}
}