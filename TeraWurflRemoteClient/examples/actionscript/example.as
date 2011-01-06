/**
 * Tera-WURFL remote webservice client for ActionScript
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 * 
 * Documentation is available at http://www.tera-wurfl.com
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