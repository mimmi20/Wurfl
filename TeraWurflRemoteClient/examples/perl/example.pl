#!/usr/bin/perl

######################################################################################
# Tera-WURFL remote webservice client for Perl
# 
# Tera-WURFL was written by Steve Kamerman, and is based on the
# Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
# This version uses a MySQL database to store the entire WURFL file, multiple patch
# files, and a persistent caching mechanism to provide extreme performance increases.
# 
# @author Steve Kamerman <stevekamerman AT gmail.com>
# @version Stable 2.1.1 (2010/02/21 17:41:47)
# @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
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

