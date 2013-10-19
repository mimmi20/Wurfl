# -*- coding: utf-8 -*-
# Python
######################################################################################
# Tera-WURFL remote webservice client for Python
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
# 
# @copyright  ScientiaMobile, Inc.
# @author     Steve Kamerman <stevekamerman AT gmail.com>
# @license    GNU Affero General Public License
# 
# Documentation is available at http://www.tera-wurfl.com
#######################################################################################

from urllib import quote, urlopen
import json
 
# Location of Tera-WURFL webservice
webservice = "http://localhost/Tera-Wurfl/webservice.php"
 
# The User Agent you would like to check
user_agent = "Mozilla/5.0 (Linux; U; Android 1.0; en-us; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2"
 
# Capabilities and Groups you want to find
search = "brand_name|model_name|marketing_name|is_wireless_device|device_claims_web_support|tera_wurfl"
 
url = "%s?format=json&ua=%s&search=%s" % (webservice, quote(user_agent), search)
json_response = urlopen(url).read()
properties = json.loads(json_response)
capabilities = properties["capabilities"]

# Tera-WURFL processing is finished,  properties and capabilities dictionaries are now filled with data
 
print "Response from Tera-WURFL " + properties['apiVersion'];
for name, value in capabilities.items():
	print "%s: %s" % (name, value)