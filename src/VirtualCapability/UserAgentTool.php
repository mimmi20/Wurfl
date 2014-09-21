<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability;

use Wurfl\Request\GenericRequest;

/**
 * Standalone utility for deriving device capabilities from a user agent
 *
 * @package \Wurfl\VirtualCapability\VirtualCapability
 */
class UserAgentTool
{
    /**
     * Gets a device from the UA
     *
     * @param \Wurfl\Request\GenericRequest $request
     *
     * @return \Wurfl\VirtualCapability\Tool\Device
     */
    public function getDevice(GenericRequest $request)
    {
        $device = $this->assignProperties(new Tool\Device($request));
        $device->normalize();

        return $device;
    }

    /**
     * Gets a device from the UA
     *
     * @param \Wurfl\VirtualCapability\Tool\Device $device
     *
     * @return \Wurfl\VirtualCapability\Tool\Device
     */
    protected function assignProperties(Tool\Device $device)
    {
        //Is UA Windows Mobile? - WP before Android
        if ($device->os->setContains($device->device_ua, 'Windows CE', 'Windows Mobile')) {
            $device->browser->set('IE Mobile');

            return $device;
        }

        if (strpos($device->device_ua, 'Windows Phone') !== false) {
            // Is UA Windows Phone OS?
            if ($device->os->setRegex(
                $device->device_ua,
                '/Windows Phone(?: OS)? ([0-9]\.[0-9])/',
                'Windows Phone',
                1
            )
            ) {
                $device->browser->set('IE Mobile');
                $device->browser->setRegex($device->device_ua, '/IEMobile\/(\d+\.\d+)/', 'IE Mobile', 1);

                return $device;
            }
        }

        //Is UA Android?
        if (strpos($device->device_ua, 'Android') !== false) {
            $device->os->setRegex($device->device_ua, '#Android(?: |/)([0-9]\.[0-9]).+#', 'Android', 1);

            //Is Dalvik?
            if (strpos($device->browser_ua, 'Dalvik') !== false) {
                $device->browser->name = 'Android App';

                if ($device->browser->setRegex($device->browser_ua, '/Android ([0-9]\.[0-9])/', null, 1)) {
                    return $device;
                }
            }

            //Is FB app?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/^Mozilla\/[45]\.0.+?Android.+?AppleWebKit.+FBAN/',
                'FaceBook Android App',
                $device->os->version
            )
            ) {
                return $device;
            }

            //Is UA Chrome Mobile?
            if ($device->browser->setRegex($device->browser_ua, '/Chrome\/([0-9]?[0-9])\.?/', 'Chrome Mobile', 1)) {
                return $device;
            }

            //Is UA Fennec?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/(?:Firefox|Fennec)\/([0-9]?[0-9]\.[0-9]?)/',
                'Firefox Mobile',
                1
            )
            ) {
                return $device;
            }

            //Is UA Opera Mobi?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/Opera Mobi\/.*Version\/([0-9]?[0-9])/',
                'Opera Mobile',
                1
            )
            ) {
                return $device;
            }

            //Is UA Opera Mini?
            if ($device->browser->setRegex($device->browser_ua, '/Opera Mini\/([0-9]+)?\.[0-9]/', 'Opera Mini', 1)) {
                return $device;
            }

            //Is UA Opera Tablet?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/Opera Tablet\/.*Version\/([0-9]?[0-9])/',
                'Opera Tablet',
                1
            )
            ) {
                return $device;
            }

            //Is UA UC Browser with UCBrowser tag?
            if ($device->browser->setRegex($device->browser_ua, '/UCBrowser\/([0-9]+)\./', 'UC Browser', 1)) {
                return $device;
            }

            //Is UA UC Browser with UCWEB tag?
            if ($device->browser->setRegex($device->browser_ua, '/^JUC.*UCWEB([0-9])/', 'UC Browser', 1)) {
                return $device;
            }

            //Is UA Amazon Silk browser?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/Silk\/([0-9]\.[0-9]).+?Silk\-Accelerated/',
                'Amazon Silk Browser',
                1
            )
            ) {
                return $device;
            }

            //Catchall for all other Android UAs including stock Webkit
            $device->browser->set('Android', $device->os->version);

            return $device;
        }

        //Is UA Amazon Silk browser without the word Android?
        if (strpos($device->device_ua, 'Silk') !== false && $device->browser->setRegex(
                $device->browser_ua,
                '/Silk\/([0-9]\.[0-9]).+?Silk\-Accelerated/',
                'Amazon Silk Browser',
                1
            ) && $device->os->set('Android', null)
        ) {
            return $device;
        }

        //Is UA iOS?
        if (strpos($device->device_ua, 'iPhone') !== false || strpos($device->device_ua, 'iPad') !== false || strpos(
                $device->device_ua,
                'iPod'
            ) !== false
        ) {
            $device->os->name = 'iOS';

            if ($device->os->setRegex(
                $device->device_ua,
                '/Mozilla\/[45]\.[0-9] \((iPhone|iPod|iPad);(?: U;)? CPU(?: iPhone|) OS ([0-9]_[0-9](?:_[0-9])?) like Mac OS X/',
                'iOS',
                2
            )
            ) {
                $device->os->version = str_replace('_', '.', $device->os->version);
            }

            //Is UA Chrome Mobile on iOS?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?CriOS\/([0-9]+?)\.[0-9].+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./',
                'Chrome Mobile iOS',
                1
            )
            ) {
                return $device;
            }

            //Is UA UC Web Browser?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+.*UCBrowser\/?([0-9]+)\./',
                'UC Web Browser on iOS',
                1
            )
            ) {
                return $device;
            }

            //Is UA Mobile iOS Safari?
            if ($device->browser->setRegex(
                $device->browser_ua,
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+.*FBAN/',
                'FaceBook app on iPhone',
                $device->os->version
            )
            ) {
                return $device;
            }

            //Catchall for all other iOS UAs including Mobile Safari
            $device->browser->set('Mobile Safari', $device->os->version);

            return $device;
        }

        //Is UA S40 Ovi Browser?
        if (strpos($device->device_ua, 'OviBrowser') !== false && $device->browser->setRegex(
                $device->browser_ua,
                '/\bS40OviBrowser\/([0-9]\.[0-9])/',
                'S40 Ovi Browser',
                1
            ) && $device->os->set('Nokia Series 40')
        ) {
            return $device;
        }

        //Is Series60?
        if ($device->os->setRegex(
            $device->device_ua,
            '#(?:SymbianOS|Series60|S60)/(\d+(?:\.\d+)?)#',
            'Symbian S60',
            1
        )
        ) {
            $device->os->setRegex($device->device_ua, '/^Mozilla\/[45]\.0 \(Symbian\/3/', 'Symbian', '^3');

            if ($device->browser->setRegex(
                $device->browser_ua,
                '/NokiaBrowser\/([0-9]\.[0-9])/',
                'Symbian S60 Browser',
                1
            )
            ) {
                return $device;
            }

            if ($device->browser->setRegex(
                $device->browser_ua,
                '/Opera Mobi.+Version\/([0-9]?[0-9]\.[0-9]?[0-9])/',
                'Opera Mobi',
                1
            )
            ) {
                return $device;
            }

            $device->browser->set('Symbian S60 Browser');

            return $device;
        }

        //Is UA Blackberry?
        if (strpos($device->device_ua, 'BlackBerry') !== false && $device->os->setRegex(
                $device->device_ua,
                '/(?:BlackBerry)|(?:^Mozilla\/5.0 \(BB10; ([a-zA-Z0-9])\))/',
                'BlackBerry'
            )
        ) {
            // Set resonable defaults
            $device->os->setRegex($device->device_ua, '/^BlackBerry[0-9A-Za-z]+?\/([0-9]\.[0-9])/', null, 1);

            if ($device->os->setRegex(
                $device->device_ua,
                '/^BlackBerry[0-9A-Za-z]+?\/([0-9]\.[0-9]).+?UC Browser\/?([0-9]\.[0-9])/',
                null,
                1
            )
            ) {
                $device->browser->set('UC Web', $device->os->getLastRegexMatch(2));

                return $device;
            }

            if ($device->os->setRegex(
                $device->device_ua,
                '/^UCWEB\/[0-9]\.0.+?; [a-zA-Z][a-zA-Z]?\-[a-zA-Z]?[a-zA-Z]; [0-9]+?\/([0-9]\.[0-9]).+?UCBrowser\/?([0-9]\.[0-9])/',
                null,
                1
            )
            ) {
                $device->browser->set('UC Web', $device->os->getLastRegexMatch(2));

                return $device;
            }

            // Is UA Opera Mini?
            if ($device->browser->setRegex($device->browser_ua, '/Opera Mini\/([0-9]\.[0-9])/', 'Opera Mini', 1)) {
                return $device;
            }

            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[45]\.0 \(BlackBerry;(?: U;)? BlackBerry.+?Version\/([0-9]\.[0-9])/',
                null,
                1
            )
            ) {
                $device->browser->set('BlackBerry Browser', $device->os->version);

                return $device;
            }

            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[45]\.0 \(BB10; .+?Version\/([0-9]\.[0-9])/',
                null,
                1
            )
            ) {
                $device->browser->set('BlackBerry Webkit Browser', $device->os->version);

                return $device;
            }

            $device->browser->set('BlackBerry Browser');
            // TODO: figure out if we need to return here
        }

        //Is UA RIM Tablet OS?
        if (strpos($device->device_ua, 'RIM Tablet OS') !== false && $device->os->setRegex(
                $device->device_ua,
                '/RIM Tablet OS ([0-9]\.[0-9]).+?Version\/([0-9]\.[0-9])/',
                'RIM Tablet OS',
                1
            )
        ) {
            $device->browser->set('RIM OS Browser', $device->os->getLastRegexMatch(2));

            return $device;
        }

        //Is UA Netfront?
        if (strpos($device->device_ua, 'NetFront') !== false && $device->browser->setRegex(
                $device->browser_ua,
                '/NetFront\/([0-9]\.[0-9])/',
                'NetFront',
                1
            )
        ) {
            return $device;
        }

        //Is UA Teleca Obigo
        if ($device->browser->setContains($device->device_ua, 'Obigo', 'Teleca Obigo') && $device->browser->setRegex(
                $device->browser_ua,
                '/Obig[a-zA-Z]+?\/(Q[0-9\.ABC]+)/',
                null,
                1
            )
        ) {
            return $device;
        }

        //Is UA Samsung's Bada OS?
        if (strpos($device->device_ua, 'Dolfin') !== false && $device->os->setRegex(
                $device->device_ua,
                '/SAMSUNG.+?\bBada\/([0-9]\.[0-9]);?.+Dolfin\/([0-9]\.[0-9])/',
                'Bada',
                1
            )
        ) {
            $device->browser->set('Dolfin Browser', $device->os->getLastRegexMatch(2));

            return $device;
        }

        //Is UA a MAUI browser?
        if ($device->browser->setContains($device->device_ua, 'MAUI', 'MAUI Browser')) {
            return $device;
        }

        //Is UA an Openwave browser?
        if (strpos($device->device_ua, 'Dolfin') !== false && $device->browser->setRegex(
                $device->browser_ua,
                '/UP\.(?:Browser|Link)\/([0-9]\.[0-9])/',
                'Openwave Browser',
                1
            )
        ) {
            return $device;
        }

        //Is UA webOS?
        if ($device->os->setRegex(
            $device->device_ua,
            '/^Mozilla\/[45]\.0 \((?:Linux; )?webOS\/([0-9]\.[0-9])/',
            'webOS',
            1
        )
        ) {
            $device->browser->set('webOS Browser', $device->os->version);

            return $device;
        }

        if (strpos($device->device_ua, 'Opera') !== false) {
            //Is UA Opera Mobi?
            if ($device->browser->setContains($device->device_ua, 'Opera Mobi', 'Opera Mobile')) {
                if ($device->browser->setRegex(
                    $device->device_ua,
                    '/Opera Mobi.+Version\/([0-9]?[0-9]\.[0-9]?[0-9])/',
                    null,
                    1
                )
                ) {
                    return $device;
                }

                return $device;
            }

            //Is UA Opera Mini?
            if ($device->browser->setRegex($device->device_ua, '/Opera Mini\/([0-9]\.[0-9])/', 'Opera Mini', 1)) {
                return $device;
            }

            //Is UA Opera Sync?
            if ($device->browser->setRegex(
                $device->device_ua,
                '/Browser\/Opera Sync\/SyncClient.+?Version\/([0-9]?[0-9]\.[0-9][0-9]?)/',
                'Opera Link Sync',
                1
            )
            ) {
                return $device;
            }
        }

        if (strpos($device->device_ua, 'Maemo') !== false) {
            $device->os->set('Maemo');
            //Maemo
            if ($device->browser->setRegex($device->browser_ua, '/Maemo.+?Firefox\/([0-9a\.]+) /', 'Firefox', 1)) {
                return $device;
            }
        }

        //Final ditch effort
        if ($device->browser->setRegex($device->browser_ua, '/(?:MIDP.+?CLDC)|(?:UNTRUSTED)/', 'Java Applet')) {
            return $device;
        }

        // Desktop Browsers

        //MSIE
        if (strpos($device->device_ua, 'Trident') !== false || strpos($device->device_ua, 'MSIE') !== false) {
            //MSIE 10 and below
            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 \(compatible; MSIE ([0-9][0-9]?\.[0-9][0-9]?); ((?:Windows NT [0-9]\.[0-9])|(?:Windows [0-9]\.[0-9])|(?:Windows [0-9]+)|(?:Mac_PowerPC))/',
                2
            )
            ) {
                $device->browser->set('IE', $device->os->getLastRegexMatch(1));

                return $device;
            } //MSIE 11 and above
            else if ($device->os->setRegex(
                $device->device_ua,
                '#^Mozilla/[45]\.0 \((Windows NT [0-9]\.[0-9]);.+Trident.+; rv:([0-9]+)\.[0-9]+#',
                1
            )
            ) {
                $device->browser->set('IE', $device->os->getLastRegexMatch(2));

                return $device;
            }
        }

        //Yandex Browser
        if (strpos($device->device_ua, 'YaBrowser') !== false && $device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[45]\.[0-9] \((?:Macintosh; )?([a-zA-Z0-9\._ ]+)\) AppleWebKit.+YaBrowser\/([0-9]?[0-9]\.[0-9])/',
                1
            )
        ) {
            $device->browser->set('Yandex browser', $device->os->getLastRegexMatch(2));

            return $device;
        }

        if (strpos($device->device_ua, 'Chrome') !== false) {
            //Chrome Mac
            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 \(Macintosh;(?: U;)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([0-9]+\.[]0-9]+)\.?/',
                1
            )
            ) {
                $device->browser->set('Chrome', $device->os->getLastRegexMatch(2));

                return $device;
            }

            //Chrome
            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 \((?:Windows;|X11;)?(?: U; )?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([0-9]+\.[]0-9]+)\.?/',
                1
            )
            ) {
                $device->browser->set('Chrome', $device->os->getLastRegexMatch(2));

                return $device;
            }
        }

        //Safari
        if (strpos($device->device_ua, 'Safari') !== false && $device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 \((?:(?:Windows|Macintosh); (?:U; |WOW64; )?)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Version\/([0-9]+\.[]0-9]+)\.?/',
                1
            )
        ) {
            $device->browser->set('Safari', $device->os->getLastRegexMatch(2));

            return $device;
        }

        if (strpos($device->device_ua, 'Firefox') !== false) {
            //Firefox - Windows
            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 .+(Windows [0-9A-Za-z \.]+;).+?rv:.+?Firefox\/([0-9]?[0-9]\.[0-9])/',
                1
            )
            ) {
                $device->browser->set('Firefox', $device->os->getLastRegexMatch(2));

                return $device;
            }

            //Firefox
            if ($device->os->setRegex(
                $device->device_ua,
                '/^Mozilla\/[0-9]\.0 \((?:X11|Macintosh); (?:U; |Ubuntu; |)((?:Intel|PPC|Linux) [a-zA-Z0-9\- \._\(\)]+);.+?rv:.+?Firefox\/([0-9]?[0-9]\.[0-9])/',
                1
            )
            ) {
                $device->browser->set('Firefox', $device->os->getLastRegexMatch(2));

                return $device;
            }
        }

        //Opera
        if (strpos($device->device_ua, 'Opera') !== false && $device->os->setRegex(
                $device->device_ua,
                '/^Opera\/([0-9]?[0-9]\.[0-9][0-9]?) .+?((?:Windows|Linux|PPC|Intel) [a-zA-Z0-9 _\.\-]+) ?;/',
                2
            )
        ) {
            $device->browser->set('Opera', $device->os->getLastRegexMatch(1));
            $device->browser->setRegex(
                $device->browser_ua,
                '/^Opera\/.+? Version\/([0-9]?[0-9]\.[0-9][0-9]?)/',
                null,
                1
            );

            return $device;
        }

        return $device;
    }
}
