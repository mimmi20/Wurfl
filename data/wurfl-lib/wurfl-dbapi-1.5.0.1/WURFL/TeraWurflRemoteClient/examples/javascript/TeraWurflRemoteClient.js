/**
 * Tera-WURFL remote webservice client for JavaScript
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
function TeraWurflRemoteClient(webservice){
	// Properties
	this.webservice = webservice;
	this.capabilities = new Array();
	this.errors = new Array();
	this.xmlHttpReq = false;
	var self = this;

	// Methods
	this.getCapabilitiesFromAgent = function(userAgent,capabilities_search){
		this.userAgent = userAgent;
		this.search = capabilities_search.join('|');
		this.fullURL = this.webservice + '?ua=' + this.urlencode(this.userAgent) + '&search=' + this.search;
		
		if(window.XMLHttpRequest) {
			self.xmlHttpReq = new XMLHttpRequest();
		}else if (window.ActiveXObject) {
			try{
				self.xmlHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
			}catch(e){
				try{
					self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
				}catch(e){}
			}
		}
		self.xmlHttpReq.open('GET', this.fullURL, true);
		self.xmlHttpReq.onreadystatechange = this.handleResponse;
		self.xmlHttpReq.send(null);
	}

	this.handleResponse = function(){
		if(self.xmlHttpReq.readyState != 4) return;
		if(self.xmlHttpReq.status == 12029 || self.xmlHttpReq.status == 12007){
			alert("Error: Could not connect to remote webservice");
			return;
		}
		if (window.ActiveXObject) { // for IE
			var doc = self.xmlHttpReq.responseXML;
		}else{ // code for Mozilla, Firefox, Opera, etc.
			var parser=new DOMParser();
			var doc=parser.parseFromString(self.xmlHttpReq.responseText,"text/xml");
		}
		try{
			var data  = "";
			if(doc.documentElement){ //Response from webservice
				data = doc.documentElement;
				self.receivedRemoteCapabilities(data);
			}else{
				data = self.xmlHttpReq.responseText; //from web page
				alert("No XML:\nresponseText["+data+"]\nresponseXML["+self.xmlHttpReq.responseXML+"]\nAll Headers:\n["+self.xmlHttpReq.getAllResponseHeaders()+"]"); 
			}
		}catch(e) {}
	}
	
	this.receivedRemoteCapabilities = function(xml_response){
		this.devices = xml_response.getElementsByTagName('device');
		this.capabilitiesXML = xml_response.getElementsByTagName('capability');
		this.errorsXML = xml_response.getElementsByTagName('error');
		var name, value, i;
		// Put capabilities into an object / associative array
		for(i=0;i<this.capabilitiesXML.length;i++){
			name = this.capabilitiesXML[i].getAttribute('name');
			value = this.capabilitiesXML[i].getAttribute('value');
			this.capabilities[name] = value;
		}
		// Put errors into an array
		for(i=0;i<this.errorsXML.length;i++){
			errname = this.errorsXML[i].getAttribute('name');
			errdesc = this.errorsXML[i].getAttribute('description');
			this.errors[i] = {name: errname, description: errdesc};
		}
		this.onUpdate(this.capabilities,this.errors);
	}
	
	this.urlencode = function(str){
		str = (str+'').toString();
		return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
	}
}
