ScientiaMobile WURFL API, Database Edition (formerly Tera-WURFL)
http://www.scientiamobile.com/
http://www.tera-wurfl.com/
-----------------------

ABOUT THE SCIENTIAMOBILE WURFL API, DATABASE EDITION

The Database-Edition WURFL API is a PHP library with database backend support
for MySQL, Microsoft SQL Server and MongoDB that uses the Wireless Universal 
Resource File (WURFL) to detect the capabilities of mobile devices.  This API
is copyrighted by ScientiaMobile, and was originally written by Steve Kamerman,
COO of ScientiaMobile, Inc.

Read more about Tera-WURFL and download the latest release at:
http://www.tera-wurfl.com/

LICENSE
This program is free software: you can redistribute it and/or modify it under
the terms of the GNU Affero General Public License as published by the Free 
Software Foundation, either version 3 of the License, or (at your option) any
later version.

Please refer to the COPYING file distributed with this package for the
complete text of the applicable GNU Affero General Public License.

If you are not able to comply with the terms of the AGPL license, commercial
licenses are available from ScientiaMobile, Inc at http://www.ScientiaMobile.com/

INSTALLATION
1. Copy the files into a directory on your webserver that can be accessed
   from the Internet.  For the purposes of explanation I will refer to this
   location as http://yourserver.com/Tera-Wurfl/, with a local directory
   of /var/www/Tera-Wurfl/.
2. Open a web browser and go to:
   http://yourserver.com/Tera-Wurfl/admin/install.php
   you should see the "Tera-WURFL Installation" page with a bunch of errors
   on it.  The next steps will show you how to correct these errors.  You 
   can refresh this page to verify that your changes have taken effect.

CONFIGURATION FILE
3. Copy TeraWurflConfig.php.example to TeraWurflConfig.php, then edit it.
   Each setting is described in comments.  The only thing you NEED to modify
   is the database info (user, pass, db, etc).

FILE PERMISSIONS
4. By default, the DATADIR is set to "data/".  This directory holds the
   wurfl.xml file, your patch file(s) and the log file.  This directory
   and EVERYTHING in it needs to be accessible (read+write) by the user
   that runs your webserver. This is normally "apache", "www-data" or 
   "nobody" in Linux. (chown -R apache:apache ./data/)
5. Verify that the permissions errors on the installation page are fixed
   by refreshing the browser.
   
DATABASE OPTIONS
6. Create a new database for Tera-WURFL and a user that has a minimum of
   the following privileges on the new database:
      SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, CREATE TMP TABLE,
      CREATE ROUTINE, ALTER ROUTINE, EXECUTE
7. In the config file, set the DB_HOST, DB_USER, DB_PASS and DB_SCHEMA.
8. Verify that the database errors on the installation page are fixed
   by refreshing the browser.  If the database tables don't exist yet
   they will be created for you when you load the WURFL file.

LOADING THE Tera-WURFL DATABASE
9. If you are satisfied with the results on the installation page, you
   can load the database with the WURFL data.  There are three sources
   that the data can come from:
   a. Your local WURFL file
      This will load the DATADIR/wurfl.xml file into your database.
   b. The current released WURFL
      This will download the current wurfl.xml file from the official
      WURFL website and load it into your database.
   c. The current CVS WURFL
      This will download the current unreleased CVS wurfl.xml file from
      the official WURFL website and load it into your database.
10.There will be a delay while the server loads/downloads the WURFL, then
   you should see something like this:

Database Update OK

Total Time: 10.211848974228
Parse Time: 0.43991899490356
Validate Time: 2.7456870079041
Sort Time: 1.3005568981171
Patch Time: 0.44084501266479
Database Time: 3.9245390892029
Cache Rebuild Time: 1.3603019714355
Number of Queries: 294
PHP Memory Usage: 213.19 MB
--------------------------------
WURFL Version: www.scientiamobile.com - 2011-06-06
WURFL Devices: 13714
PATCH New Devices: 3036
PATCH Merged Devices: 2

Update Complete!
   
   If there are errors, they are most likely problems connecting to the WURFL
   site or permission problems trying to write the temporary downloaded file
   to the DATADIR directory.  Also, many users have reported a "memory allocation"
   error - to fix this look for "OVERRIDE_MEMORY_LIMIT" in the config file. If 
   there are no errors, click on "Return to administration tool".

ADMINISTRATION PAGE
11.Verify that the WURFL is loaded by clicking on "Statistics, Settings,
   Log File".  You should see that there is data in your MERGE and INDEX
   tables. The CACHE table will fill up with cached data as devices access
   your site, and it will be cleared ONLY if you click on "Clear the device
   cache" from main administration page.
12.Click on "Tera-WURFL test script" to test the installation.  You can
   type in any user agent to search for it in the WURFL.  If you have caching
   enabled (default), the first time you search for a user agent you will see
   the total number of queries that Tera-WURFL performed to find a match
   (unless there was no match).  If there was a match, the device will be
   cached and the next time you search for it you will see "Total Queries: 1
   (Found in cache)".  Tera-WURFL only needed to perform 1 query since the
   devices full capabilities were cached.  If you go back to the admin page
   and look at the settings, you will see that the CACHE now has
   a couple rows in it.  You can verify the device was cached properly by going
   to "Cache Browser" from the main page and clicking on your user agent.
   
SECURITY
13.You should delete the admin/install.php file if everything is working ok.
14.Protect your 'admin' directory by using a method specific to your web
   server software.  For Apache, this means a .htaccess file would work:
   http://www.mobilefish.com/developer/apache/apache_quickguide_htaccess.html#protect

EXAMPLE
Here is a very simple example:
<?php
// Include the Tera-WURFL file
require_once('./TeraWurfl.php');

// instantiate the Tera-WURFL object
$wurflObj = new TeraWurfl();

// Get the capabilities of the current client.
$wurflObj->getDeviceCapabilitiesFromAgent();

// see if this client is on a wireless device (or if they can't be identified)
if(!$wurflObj->getDeviceCapability("is_wireless_device")){
	echo "<h2>You should not be here</h2>";
}

// see what this device's preferred markup language is
echo "Markup: ".$wurflObj->getDeviceCapability("preferred_markup");

// see the display resolution
$width = $wurflObj->getDeviceCapability("resolution_width");
$height = $wurflObj->getDeviceCapability("resolution_height");
echo "<br/>Resolution: $width x $height<br/>";
?>

The complete list of capabilities is available here:
http://wurfl.sourceforge.net/help_doc.php#product_info
If you use $wurflObj->getDeviceCapability("cap_name") you can just use the capability name from
the WURFL site, otherwise you can access it directly from the wurfl object's
capabilities array like this: $wurflObj->capabilities['group_name']['cap_name']

Please see the test script (check_wurfl.php) for another example of usage.

SUPPORT
Commercial support licenses are available from ScientiaMobile at http://www.ScientiaMobile.com/
Please direct your questions to the ScientiaMobile forum at http://www.ScientiaMobile.com/forum
You may also find useful information on the legacy Tera-WURFL Forum: http://www.tera-wurfl.com/forum
