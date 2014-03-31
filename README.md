Wurfl
=====

an clone of the official Wurfl PHP library updated for PHP 5.3

[![Build Status](https://api.travis-ci.org/mimmi20/Wurfl.png?branch=master)](https://travis-ci.org/mimmi20/Wurfl)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/mimmi20/Wurfl/badges/quality-score.png?s=5e88e19d3a659f74ca468170d70c30c94c4ab2c0)](https://scrutinizer-ci.com/g/mimmi20/Wurfl/)
[![Code Coverage](https://scrutinizer-ci.com/g/mimmi20/Wurfl/badges/coverage.png?s=b9a661d611e63c513c3d6800572c3f06e520bae4)](https://scrutinizer-ci.com/g/mimmi20/Wurfl/)

Submitting bugs and feature requests
------------------------------------

Bugs and feature request are tracked on [GitHub](https://github.com/mimmi20/Wurfl/issues)

Important changes
-----------------

These changes are made:
- added Wurfl namespace, removed the part "WURFL" from the filenames
- merged the \Wurfl\Service and \Wurfl\ManagerFactory into \Wurl\Manager

# the official WURFL PHP API #
==============================

- http://www.scientiamobile.com/
- http://wurfl.sourceforge.com/

----------

# LICENSE #
This program is free software: you can redistribute it and/or modify it under
the terms of the GNU Affero General Public License as published by the Free
Software Foundation, either version 3 of the License, or (at your option) any
later version.

Please refer to the COPYING file distributed with this package for the
complete text of the applicable GNU Affero General Public License.

If you are not able to comply with the terms of the AGPL license, commercial
licenses are available from ScientiaMobile, Inc at http://www.ScientiaMobile.com/

# Getting Started #
Download a release archive from wurfl site and extract it to a directory
suitable for your application.

To start using the API you need to set some configuration options.

> __IMPORTANT__: The WURFL API is closely tied to the WURFL.XML file.  New versions of the WURFL.XML are compatible with old versions of the API by nature, but the reverse is NOT true.  Old versions of the WURFL.XML are NOT guarenteed to be compatible with new versions of the API.  Let's restate that: This API is NOT compatible with old versions of the WURFL.XML.  The oldest copy of WURFL that can be used with this API is included in this distribution.

```php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 *
 * change this to pass to your project settings
 */
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/*
 * relative to the root dir
 * -> $resourcesDir = <Project Root>/resources
 */
$resourcesDir   = 'resources';
$persistenceDir = $resourcesDir . '/storage/persistence';
$cacheDir       = $resourcesDir . '/storage/cache';

// Create WURFL Configuration
$wurflConfig = new \Wurfl\Configuration\InMemoryConfig();

// Set location of the WURFL File
$wurflConfig->wurflFile($resourcesDir . '/wurfl.zip');

/*
 * Set the match mode for the API
 *
 * It is recommended to use the defined class constants instead of their
 * string values:
 *
 * \Wurfl\Configuration\Config::MATCH_MODE_PERFORMANCE
 * \Wurfl\Configuration\Config::MATCH_MODE_ACCURACY
 */
$wurflConfig->matchMode(\Wurfl\Configuration\Config::MATCH_MODE_PERFORMANCE);

// Setup WURFL Persistence
$wurflConfig->persistence(
    'file',
    array(\Wurfl\Configuration\Config::DIR => $persistenceDir)
);

// Setup Caching
$wurflConfig->cache(
    'file',
    array(
        \Wurfl\Configuration\Config::DIR        => $cacheDir,
        \Wurfl\Configuration\Config::EXPIRATION => 36000
    )
);

// Create a WURFL Manager from the WURFL Configuration
$wurflManager = new \Wurfl\Manager($wurflConfig);
```

Now you can use some of the `\Wurfl\Manager` class methods;

```php
$device = $wurflManager->getDeviceForHttpRequest($_SERVER);
$device->getCapability('is_wireless_device');
$device->getVirtualCapability('is_smartphone');
```

## Create a configuration object ##

1. Set the paths to the location of the main WURFL file
    (you can use zip files if you have the zip extension enabled)

2. Configure the Persistence provider by specifying the provider
    and the extra parameters needed to initialize the provider.
    The API supports the following mechanisms:
    - Memcache (http://uk2.php.net/memcache)
    - APC (Alternative PHP Cache http://www.php.net/apc)
    - MySQL
    - Memory
    - File

    Additional to the official providers the following connectots are added:
    - Zend Cache

3. Configure the Cache provider by specifying the provider
    and the extra parameters needed to initialize the provider.
    The API supports the following caching mechanisms:
    - Memcache (http://uk2.php.net/memcache)
    - APC (Alternative PHP Cache http://www.php.net/apc)
    - File
    - Null (no caching)

    Additional to the official providers the following connectots are added:
    - Zend Cache

### Example Configuration ###
```php
// Create WURFL Configuration
$wurflConfig = new \Wurfl\Configuration\InMemoryConfig();

// Set location of the WURFL File
$wurflConfig->wurflFile($resourcesDir . '/wurfl.zip');

/*
 * Set the match mode for the API
 *
 * It is recommended to use the defined class constants instead of their
 * string values:
 *
 * \Wurfl\Configuration\Config::MATCH_MODE_PERFORMANCE
 * \Wurfl\Configuration\Config::MATCH_MODE_ACCURACY
 */
$wurflConfig->matchMode(\Wurfl\Configuration\Config::MATCH_MODE_PERFORMANCE);

// Setup WURFL Persistence
$wurflConfig->persistence(
    'file',
    array(\Wurfl\Configuration\Config::DIR => $persistenceDir)
);

// Setup Caching
$wurflConfig->cache(
    'file',
    array(
        \Wurfl\Configuration\Config::DIR        => $cacheDir,
        \Wurfl\Configuration\Config::EXPIRATION => 36000
    )
);
```

## Using the WURFL PHP API ##

### Getting the device ###

You have four methods for retrieving a device:

1. `getDeviceForRequest(\Wurfl\Request\GenericRequest $request)`
    where a \Wurfl\Request\GenericRequest is an object which encapsulates
    userAgent, ua-profile, support for xhtml of the device.

2. `getDeviceForHttpRequest($_SERVER)`
    Most of the time you will use this method, and the API will create the
    the \Wurfl\Request\GenericRequest object for you

3. `getDeviceForUserAgent(string $userAgent)`
    Used to query the API for a given User Agent string

4. `getDevice(string $deviceID)`
    Gets the device by its device ID (ex: `apple_iphone_ver1`)

Usage example:
```php
$device = $wurflManager->getDeviceForHttpRequest($_SERVER);
```

### Getting the device properties and its capabilities ###

The properties Device ID and Fall Back Device ID are properties of the device:

```php
$deviceID = $device->id;
$fallBack = $device->fallBack;
```

To get the value of a capability, use `getCapability()`:

```php
$value   = $device->getCapability("is_tablet");
$allCaps = $device->getAllCapabilities();
```

To get the value of a virtual capability, use `getVirtualCapability()`:

```php
$value    = $device->getVirtualCapability("is_smartphone");
$allVCaps = $device->getAllVirtualCapabilities();
```

### Useful Methods ###
The root WURFL device object has some useful functions:

```php
/* @var $device \Wurfl\CustomDevice */
$device = $wurflManager->getDeviceForHttpRequest($_SERVER);

/* @var $root \Wurfl\Xml\ModelDevice */
$root = $device->getRootDevice();

$group_names = $root->getGroupNames();
$cap_names   = $root->getCapNames();
$defined     = $root->isCapabilityDefined("foobar");
```


### WURFL Reloader ###
WURFL can update the persistence data automatically without any configuration
by checking the modification time of the WURFL file.  To enable, set
allow-reload to true in the config:

```php
$wurflConfig->allowReload(true);
```

If you have any questions, please take a look at the documentation on http://wurfl.sourceforge.net,
and/or the ScientiaMobile Support Forum at http://www.scientiamobile.com/forum


