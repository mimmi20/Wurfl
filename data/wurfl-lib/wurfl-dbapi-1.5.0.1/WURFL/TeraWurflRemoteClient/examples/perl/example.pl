#!/usr/bin/perl

######################################################################################
# Tera-WURFL remote webservice client for Perl
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

use strict;
use URI;
use LWP::Simple;
use XML::Simple;

# Location of Tera-WURFL webservice
my $webservice = URI->new("http://localhost/Tera-Wurfl/webservice.php");

# The User Agent you would like to check
my $user_agent = "Mozilla/5.0 (Linux; U; Android 1.0; en-us; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2";

# Capabilities and Groups you want to find
my $search = "brand_name|model_name|marketing_name|is_wireless_device|device_claims_web_support|tera_wurfl";

# Build the query String
$webservice->query_form(
	"ua" => $user_agent,
	"search" => $search
);

# Make webservice request
my $xml_response = get $webservice;
# Parse webserver response
my $xml_parser = new XML::Simple(forcearray => 1, keyattr => ['key']);
my $xml_object = $xml_parser->XMLin($xml_response);
# Convert XML Object into Perl Hash
my %capabilities;
foreach(@{$xml_object->{device}[0]->{capability}}){
	$capabilities{$_->{name}}=$_->{value};
}
# Make top-level properties available in hash
my %properties = (
	"apiVersion", $xml_object->{device}[0]->{apiVersion},
	"id", $xml_object->{device}[0]->{id},
	"user_agent", $xml_object->{device}[0]->{useragent}
);

# Tera-WURFL proccessing is finished, capabilities are available in %capabilities, properties in %properties

print "-- Response from Tera-WURFL $properties{apiVersion}\n";
print "-- Device Detected as: $capabilities{brand_name} $capabilities{model_name} $capabilities{marketing_name}\n";

my($name,$value);
while(($name,$value) = each(%capabilities)){
	print "$name: $value\n";
}

