<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Tool;

use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;

/**
 * Standalone utility for deriving device capabilities from a user agent
 */
class DeviceFactory
{
    /**
     * @var array
     */
    private static $windowsMap = array(
        '3.1'  => 'NT 3.1',
        '3.5'  => 'NT 3.5',
        '4.0'  => 'NT 4.0',
        '5.0'  => '2000',
        '5.1'  => 'XP',
        '5.2'  => 'XP',
        '6.0'  => 'Vista',
        '6.1'  => '7',
        '6.2'  => '8',
        '6.3'  => '8.1',
        '6.4'  => '10',
        '10.0' => '10',
    );

    private static $trident_map = array(
        '7' => '11',
        '6' => '10',
        '5' => '9',
        '4' => '8',
    );

    private static $wds_map = array(
        '7.10' => '7.5',
        '8.10' => '8.1',
        '8.15' => '10',
    );

    /**
     * Gets a device from the UA
     *
     * @param \Wurfl\Request\GenericRequest $request
     * @param \Wurfl\CustomDevice           $customDevice
     *
     * @return \Wurfl\VirtualCapability\Tool\Device
     */
    public static function build(GenericRequest $request, CustomDevice $customDevice)
    {
        $device = new Device($request);

        self::assignProperties($device, $customDevice);
        self::normalizeOS($device);
        self::normalizeBrowser($device);

        return $device;
    }

    /**
     * Gets a device from the UA
     *
     * @param \Wurfl\VirtualCapability\Tool\Device $device
     * @param \Wurfl\CustomDevice                  $customDevice
     */
    private static function assignProperties(Device $device, CustomDevice $customDevice)
    {
        //Is UA Windows Mobile?
        if ($device->getOs()->setContains($device->getDeviceUa(), 'Windows CE', 'Windows Mobile')) {
            $device->getBrowser()->set('IE Mobile');

            return;
        }

        //Is UA Windows Phone OS?
        if (strpos($device->getDeviceUa(), 'Windows Phone') !== false || strpos($device->getDeviceUa(), '; wds') !== false) {
            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '/Windows Phone(?: OS)? ([\d\.]+)/',
                'Windows Phone',
                1
            ) || $device->getOs()->setRegex(
                $device->getDeviceUa(),
                '#UCWEB/\d\.\d \(Windows;.+?; wds ?([\d\.]+?);.+UCBrowser#',
                'Windows Phone',
                1
            )
            ) {
                if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/UCBrowser\/([\d\.]+)\./', 'UC Browser', 1)) {
                    return;
                }
                if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/IEMobile\/([\d\.]+)/', 'IE Mobile', 1)) {
                    return;
                }
            }
        }

        //Is UA Android?
        if (strpos($device->getDeviceUa(), 'Android') !== false || strpos($device->getDeviceUa(), ' Adr ') !== false) {
            $device->getOs()->setRegex($device->getDeviceUa(), '#Android(?: |/)([\d\.]+).+#', 'Android', 1);
            $device->getOs()->setRegex($device->getDeviceUa(), '# Adr(?: |/)([\d\.]+).+#', 'Android', 1);

            //Is Dalvik?
            if (strpos($device->getBrowserUa(), 'Dalvik') !== false) {
                $device->getBrowser()->name = 'Android App';

                if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/Android ([\d\.]+)/', null, 1)) {
                    return;
                }
            }

            //Is FB app?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?Android.+?AppleWebKit.+FBAN/',
                'FaceBook Android App',
                $device->getOs()->version
            )
            ) {
                return;
            }

            //Is UA Opera?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/OPR\/([\d\.]+)\.?/', 'Opera', 1)) {
                return;
            }

            //Is 360Browser?
            if (strpos($device->getBrowserUa(), 'Aphone Browser') !== false || strpos(
                $device->getBrowserUa(),
                '360browser'
            ) !== false
            ) {
                $device->getBrowser()->set('360 Browser', null);

                return;
            }

            //Is UA Samsung Browser?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '#SamsungBrowser/([\d\.]+) Chrome/[\d\.]+#',
                'Samsung Browser',
                1
            )
            ) {
                return;
            }

            //Is UA Chromium?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Version\/.+?Chrome\/([\d\.]+)\.?/',
                'Chromium',
                1
            )
            ) {
                return;
            }

            //Is UA Chrome Mobile?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/Chrome\/([\d\.]+)\.?/', 'Chrome Mobile', 1)) {
                return;
            }

            //Is UA Fennec?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/(?:Firefox|Fennec)\/([\d\.]+)/',
                'Firefox Mobile',
                1
            )
            ) {
                return;
            }

            //Is UA Opera Mobi?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Opera Mobi\/.*Version\/([\d\.]+)/',
                'Opera Mobile',
                1
            )
            ) {
                return;
            }

            //Is UA Opera Mini?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/Opera Mini\/([\d\.]+)/', 'Opera Mini', 1)) {
                return;
            }

            //Is UA Opera Tablet?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Opera Tablet\/.*Version\/([\d\.]+)/',
                'Opera Tablet',
                1
            )
            ) {
                return;
            }

            //Is UA UC Browser with UCBrowser tag?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/UCBrowser\/([\d\.]+)\./', 'UC Browser', 1)) {
                return;
            }

            //Is UA UC Browser with UCWEB tag?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/^JUC.*UCWEB([0-9])/', 'UC Browser', 1)) {
                return;
            }

            //Is UA Amazon Silk browser?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Silk\/([\d\.]+).+?Silk\-Accelerated/',
                'Amazon Silk Browser',
                1
            )
            ) {
                return;
            }
            //Is UA Android Webkit UA
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Version\/\d/',
                'Android Webkit',
                $device->getOs()->version
            )
            ) {
                return;
            }
            //Catchall for all other Android UAs
            $device->getBrowser()->set('Android', $device->getOs()->version);

            return;
        }

        //Is UA Amazon Silk browser without the word Android?
        if (strpos($device->getDeviceUa(), 'Silk') !== false && $device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/Silk\/([\d\.]+).+?Silk\-Accelerated/',
            'Amazon Silk Browser',
            1
        ) && $device->getOs()->set('Android', null)
        ) {
            return;
        }

        //Is UA iOS?
        if (strpos($device->getDeviceUaNormalized(), 'iPhone') !== false || strpos(
            $device->getDeviceUaNormalized(),
            'iPad'
        ) !== false || strpos($device->getDeviceUaNormalized(), 'iPod') !== false || strpos(
            $device->getDeviceUaNormalized(),
            '(iOS;'
        ) !== false
        ) {
            $device->getOs()->name = 'iOS';

            if ($device->getOs()->setRegex(
                $device->getDeviceUaNormalized(),
                '/Mozilla\/[45]\.[0-9] \((iPhone|iPod|iPad);(?: U;)? CPU(?: iPhone|) OS ([0-9]_[0-9](?:_[0-9])?) like Mac OS X/',
                'iOS',
                2
            )
            ) {
                $device->getOs()->version = str_replace('_', '.', $device->getOs()->version);
            }

            // Get Device OS version for UCBrowser 2K?
            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '#UCWEB/[\d\.]+ \(iOS;.+?OS ([\d_]+);.+UCBrowser/#',
                'iOS',
                1
            )
            ) {
                $device->getOs()->version = str_replace('_', '.', $device->getOs()->version);
            }

            //Is UA Chrome Mobile on iOS?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?CriOS\/([\d\.]+).+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./',
                'Chrome Mobile on iOS',
                1
            )
            ) {
                return;
            }

            //Is UA Firefox on iOS?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?FxiOS\/([\d\.]+).+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./',
                'Firefox on iOS',
                1
            )
            ) {
                return;
            }

            //Is UA Opera Mini on iOS?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?OPiOS\/([\d\.]+).+?Mobile\/[0-9A-Za-z]+ Safari\/[0-9A-Za-z]+\./',
                'Opera Mini on iOS',
                1
            )
            ) {
                return;
            }

            //Is UA UC Web Browser?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?OS \d_\d.+?like Mac OS X.+?AppleWebKit.+?.+UCBrowser\/?([\d\.]+)\./',
                'UC Web Browser on iOS',
                1
            )
            ) {
                return;
            }

            // Is UA UC Web Browser 2K?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '#UCWEB/\d\.\d \(iOS;.+?OS [\d_]+;.+UCBrowser/([\d\.]+)#',
                'UC Web Browser on iOS',
                1
            )
            ) {
                return;
            }

            //Is UA Facebook on iOS?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Mozilla\/[45]\.0.+?like Mac OS X.+?AppleWebKit.+?Mobile\/[0-9A-Za-z]+.*FBAN/',
                'FaceBook on iOS',
                $device->getOs()->version
            )
            ) {
                return;
            }

            // Is UA iOS Safari?
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '#^Mozilla.+like Mac OS X.+Version/([\d\.]+)#',
                'Mobile Safari',
                1
            )
            ) {
                return;
            }

            //Catchall for all other iOS UAs including Mobile Safari
            $device->getBrowser()->set('Mobile Safari', $device->getOs()->version);

            return;
        }

        //Is UA S40 Ovi Browser?
        if (strpos($device->getDeviceUa(), 'OviBrowser') !== false && $device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/\bS40OviBrowser\/([\d\.]+)/',
            'S40 Ovi Browser',
            1
        ) && $device->getOs()->set('Nokia Series 40')
        ) {
            return;
        }

        //Is Series60?
        if ($device->getOs()->setRegex(
            $device->getDeviceUa(),
            '#(?:SymbianOS|Series60|S60)/([\d\.]+)#',
            'Symbian S60',
            1
        ) || $device->getOs()->setRegex($device->getDeviceUa(), '#UCWEB/\d\.\d \(Symbian;.+?S60 V([\d\.]+)#', 'Symbian S60', 1)
        ) {
            if ($device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[45]\.0 \(Symbian\/3/', 'Symbian', '^3')) {
                ; // nothing to do here
            }

            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/NokiaBrowser\/([\d\.]+)/',
                'Symbian S60 Browser',
                1
            )
            ) {
                return;
            }
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/Opera Mobi.+Version\/([\d\.]+)/',
                'Opera Mobi',
                1
            )
            ) {
                return;
            }
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '#UCWEB/\d\.\d \(Symbian;.+?UCBrowser/([\d\.]+)#',
                'UC Web Browser on Symbian',
                1
            )
            ) {
                return;
            }
            $device->getBrowser()->set('Symbian S60 Browser');

            return;
        }

        //Is UA Blackberry?
        if (strpos($device->getDeviceUa(), 'BlackBerry') !== false
            || strpos($device->getDeviceUa(), '(BB10; ') !== false
        ) {
            // Set resonable defaults
            $device->getOs()->setRegex($device->getDeviceUa(), '/(?:BlackBerry)|(?:^Mozilla\/5.0 \(BB10; )/', 'BlackBerry');
            $device->getOs()->setRegex($device->getDeviceUa(), '/^BlackBerry[0-9A-Za-z]+?\/([\d\.]+)/', null, 1);

            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '/^BlackBerry[0-9A-Za-z]+?\/([\d\.]+).+?UC Browser\/?([\d\.]+)/',
                null,
                1
            )
            ) {
                $device->getBrowser()->set('UC Web', $device->getOs()->getLastRegexMatch(2));

                return;
            }

            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '/^UCWEB\/[0-9]\.0.+?; [a-zA-Z][a-zA-Z]?\-[a-zA-Z]?[a-zA-Z]; [0-9]+?\/([\d\.]+).+?UCBrowser\/?([\d\.]+)/',
                null,
                1
            )
            ) {
                $device->getBrowser()->set('UC Web', $device->getOs()->getLastRegexMatch(2));

                return;
            }

            // Is UA Opera Mini?
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/Opera Mini\/([\d\.]+)/', 'Opera Mini', 1)) {
                return;
            }

            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '/^Mozilla\/[45]\.0 \(BlackBerry;(?: U;)? BlackBerry.+?Version\/([\d\.]+)/',
                null,
                1
            )
            ) {
                $device->getBrowser()->set('BlackBerry Browser', $device->getOs()->version);

                return;
            }

            if ($device->getOs()->setRegex($device->getDeviceUa(), '#^Mozilla/[45]\.0 \(BB10; .+?Version/([\d\.]+)#', null, 1)) {
                $device->getBrowser()->set('BlackBerry Webkit Browser', $device->getOs()->version);

                return;
            }

            $device->getBrowser()->set('BlackBerry Browser', $device->getOs()->version);

            return;
        }

        //Is UA RIM Tablet OS?
        if (strpos($device->getDeviceUa(), 'RIM Tablet OS') !== false && $device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/RIM Tablet OS ([\d\.]+).+?Version\/([\d\.]+)/',
            'RIM Tablet OS',
            1
        )
        ) {
            $device->getBrowser()->set('RIM OS Browser', $device->getOs()->getLastRegexMatch(2));

            return;
        }

        //Is UA Netfront?
        if (strpos($device->getDeviceUa(), 'NetFront') !== false && $device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/NetFront\/([\d\.]+)/',
            'NetFront',
            1
        )
        ) {
            return;
        }

        //Is UA Teleca Obigo
        if ($device->getBrowser()->setContains($device->getDeviceUa(), 'Obigo', 'Teleca Obigo') && $device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/Obig[a-zA-Z]+?\/(Q[0-9\.ABC]+)/',
            null,
            1
        )
        ) {
            return;
        }

        //Is UA Samsung's Bada OS?
        if (strpos($device->getDeviceUa(), 'Dolfin') !== false && $device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/SAMSUNG.+?\bBada\/([\d\.]+);?.+Dolfin\/([\d\.]+)/',
            'Bada',
            1
        )
        ) {
            $device->getBrowser()->set('Dolfin Browser', $device->getOs()->getLastRegexMatch(2));

            return;
        }

        //Is UA a MAUI browser?
        if ($device->getBrowser()->setContains($device->getDeviceUa(), 'MAUI', 'MAUI Browser')) {
            return;
        }

        //Is UA an Openwave browser?
        if (strpos($device->getDeviceUa(), 'Dolfin') !== false && $device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/UP\.(?:Browser|Link)\/([\d\.]+)/',
            'Openwave Browser',
            1
        )
        ) {
            return;
        }

        //Is UA webOS?
        if ($device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/^Mozilla\/[45]\.0 \((?:Linux; )?webOS\/([\d\.]+)/',
            'webOS',
            1
        )
        ) {
            $device->getBrowser()->set('webOS Browser', $device->getOs()->version);

            return;
        }

        if (strpos($device->getDeviceUa(), 'Opera') !== false) {
            //Is UA Opera Mobi?
            if ($device->getBrowser()->setContains($device->getDeviceUa(), 'Opera Mobi', 'Opera Mobile')) {
                if ($device->getBrowser()->setRegex(
                    $device->getDeviceUa(),
                    '/Opera Mobi.+Version\/([0-9]?[0-9]\.[0-9]?[0-9])/',
                    null,
                    1
                )
                ) {
                    return;
                }

                return;
            }

            //Is UA Opera Mini?
            if ($device->getBrowser()->setRegex($device->getDeviceUa(), '/Opera Mini\/([\d\.]+)/', 'Opera Mini', 1)) {
                return;
            }

            //Is UA Opera Sync?
            if ($device->getBrowser()->setRegex(
                $device->getDeviceUa(),
                '/Browser\/Opera Sync\/SyncClient.+?Version\/([\d\.]+)/',
                'Opera Link Sync',
                1
            )
            ) {
                return;
            }
        }

        if (strpos($device->getDeviceUa(), 'Maemo') !== false) {
            $device->getOs()->set('Maemo');
            //Maemo
            if ($device->getBrowser()->setRegex($device->getBrowserUa(), '/Maemo.+?Firefox\/([0-9a\.]+) /', 'Firefox', 1)) {
                return;
            }
        }
        //UCBrowser on Java devices
        if (strpos($device->getDeviceUa(), 'Java') !== false && strpos($device->getDeviceUa(), 'UCBrowser/') !== false) {
            if ($device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '#UCWEB/\d\.\d \(Java;.+?UCBrowser/([\d\.]+)#',
                'UCBrowser Java Applet',
                1
            )
            ) {
                return;
            }
        }
        //Final ditch effort
        if ($device->getBrowser()->setRegex(
            $device->getBrowserUa(),
            '/(?:MIDP.+?CLDC)|(?:UNTRUSTED)|(?:MIDP-2.0)/',
            'Java Applet'
        )
        ) {
            return;
        }

        // Desktop Browsers
        //360 Browser
        if ((strpos($device->getDeviceUa(), '360Browser') !== false || strpos(
            $device->getDeviceUa(),
            ' 360SE'
        ) !== false) && $device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/^Mozilla\/[0-9]\.0 .+?((?:Windows|Linux|PPC|Intel) [a-zA-Z0-9 _\.\-]+).+(?:360Browser|360SE)/',
            1
        )
        ) {
            $device->getBrowser()->set('360 Browser', null);

            return;
        }
        //MSIE - If UA says MSIE
        if (strpos($device->getDeviceUa(), 'MSIE') !== false) {
            if ($device->getOs()->setRegex(
                $device->getDeviceUa(),
                '/^Mozilla\/[0-9]\.0 \(compatible; MSIE ([0-9][0-9]?\.[0-9][0-9]?); ((?:Windows NT [0-9]\.[0-9])|(?:Windows [0-9]\.[0-9])|(?:Windows [0-9]+)|(?:Mac_PowerPC))/',
                2
            )
            ) {
                $device->getBrowser()->set('IE', $device->getOs()->getLastRegexMatch(1));

                return;
            }
        }
        //MSIE - If UA says Trident - This logic must stay above Chrome
        if (strpos($device->getDeviceUa(), 'Trident') !== false || strpos($device->getDeviceUa(), ' Edge/') !== false) {
            //MSIE 11 does not say MSIE and needs this
            if ($device->getOs()->setRegex($device->getDeviceUa(), '#^Mozilla/[45]\.0 \((Windows NT [0-9]+\.[0-9]);.+Trident.+; rv:([0-9]+)\.[0-9]+#', 1)) {
                $device->getBrowser()->set('IE', $device->getOs()->getLastRegexMatch(2));

                return;
            }

            if ($device->getOs()->setRegex($device->getDeviceUa(), '#^Mozilla/[45]\.0 \((Windows NT [\d\.]+).+? Edge/([\d\.]+)#', 1)) {
                $device->getBrowser()->set('Edge', $device->getOs()->getLastRegexMatch(2));

                return;
            }
        }

        //Yandex Browser
        if (strpos($device->getDeviceUa(), 'YaBrowser') !== false && $device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/^Mozilla\/[45]\.[0-9] \((?:Macintosh; )?([a-zA-Z0-9\._ ]+)\) AppleWebKit.+YaBrowser\/([0-9]?[0-9]\.[0-9])/',
            1
        )
        ) {
            $device->getBrowser()->set('Yandex browser', $device->getOs()->getLastRegexMatch(2));

            return;
        }
        //Opera - OPR
        if (strpos($device->getDeviceUa(), 'OPR') !== false
            && $device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[0-9]\.0 .+?((?:Windows|Linux|PPC|Intel) [a-zA-Z0-9 _\.\-]+).+Chrome\/.+OPR\/([\d\.]+)/', 1)
        ) {
            $device->getBrowser()->set('Opera', $device->getOs()->getLastRegexMatch(2));

            return;
        }
        //Opera - Old UA
        if (strpos($device->getDeviceUa(), 'Opera') !== false && $device->getOs()->setRegex(
            $device->getDeviceUa(),
            '/^Opera\/([\d\.]+) .+?((?:Windows|Linux|PPC|Intel) [a-zA-Z0-9 _\.\-]+) ?;/',
            2
        )
        ) {
            $device->getBrowser()->set('Opera', $device->getOs()->getLastRegexMatch(1));
            $device->getBrowser()->setRegex(
                $device->getBrowserUa(),
                '/^Opera\/.+? Version\/([\d\.]+)/',
                null,
                1
            );

            return;
        }

        if (strpos($device->getDeviceUa(), 'Chrome') !== false) {
            //Chrome Mac
            if ($device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[0-9]\.0 \(Macintosh;(?: U;)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([\d\.]+)\.?/', 1)) {
                $device->getBrowser()->set('Chrome', $device->getOs()->getLastRegexMatch(2));

                return;
            }

            //Chrome
            if ($device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[0-9]\.0 \((?:Windows;|X11;)?(?: U; )?([a-zA-Z_ \.0-9]+)(?:;)?.+? Chrome\/([\d\.]+)\.?/', 1)) {
                $device->getBrowser()->set('Chrome', $device->getOs()->getLastRegexMatch(2));

                return;
            }
        }

        //Safari
        if (strpos($device->getDeviceUaNormalized(), 'Safari') !== false
            && $device->getOs()->setRegex($device->getDeviceUaNormalized(), '/Mozilla\/[0-9]\.0 \((?:(?:Windows|Macintosh); (?:U; |WOW64; )?)?([a-zA-Z_ \.0-9]+)(?:;)?.+? Version\/([\d\.]+)\.?/', 1)
        ) {
            $device->getBrowser()->set('Safari', $device->getOs()->getLastRegexMatch(2));

            return;
        }

        if (strpos($device->getDeviceUa(), 'Firefox') !== false) {
            //Firefox - Windows
            if ($device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[0-9]\.0 .+(Windows [0-9A-Za-z \.]+;).+?rv:.+?Firefox\/([\d\.]+)/', 1)) {
                $device->getBrowser()->set('Firefox', $device->getOs()->getLastRegexMatch(2));

                return;
            }

            //Firefox
            if ($device->getOs()->setRegex($device->getDeviceUa(), '/^Mozilla\/[0-9]\.0 \((?:X11|Macintosh); (?:U; |Ubuntu; |)((?:Intel|PPC|Linux) [a-zA-Z0-9\- \._\(\)]+);.+?rv:.+?Firefox\/([\d\.]+)/', 1)) {
                $device->getBrowser()->set('Firefox', $device->getOs()->getLastRegexMatch(2));

                return;
            }
        }

        // Is UA CFNetwork?
        if (strpos($device->getBrowserUa(), 'CFNetwork') !== false) {
            $device->getOs()->set($customDevice->getCapability('device_os'), $customDevice->getCapability('device_os_version'));
            $device->getBrowser()->set('CFNetwork App', $customDevice->getCapability('mobile_browser_version'));

            return;
        }

        return;
    }

    /**
     * normalize the OS Information
     *
     * @param \Wurfl\VirtualCapability\Tool\Device $device
     */
    private static function normalizeOS(Device $device)
    {
        if (strpos($device->getDeviceUa(), 'Windows') !== false) {
            if (preg_match('/Windows NT ([0-9]+?\.[0-9])/', $device->getOs()->name, $matches)) {
                $device->getOs()->name    = 'Windows';
                $device->getOs()->version = array_key_exists($matches[1], self::$windowsMap) ? self::$windowsMap[$matches[1]]
                    : $matches[1];

                return;
            }

            if (preg_match('/Windows [0-9\.]+/', $device->getOs()->name)) {
                return;
            }
        }

        if (strpos($device->getOs()->name, 'Windows Phone') !== false) {
            if (array_key_exists($device->getOs()->version, self::$wds_map)) {
                $device->getOs()->version = self::$wds_map[$device->getOs()->version];

                return;
            }
        }

        if ($device->getOs()->setRegex($device->getDeviceUa(), '/PPC.+OS X ([0-9\._]+)/', 'Mac OS X')) {
            $device->getOs()->version = str_replace('_', '.', $device->getOs()->version);

            return;
        }

        if ($device->getOs()->setRegex($device->getDeviceUa(), '/PPC.+OS X/', 'Mac OS X')) {
            return;
        }

        if ($device->getOs()->setRegex($device->getDeviceUa(), '/Intel Mac OS X ([0-9\._]+)/', 'Mac OS X', 1)) {
            $device->getOs()->version = str_replace('_', '.', $device->getOs()->version);

            return;
        }

        if ($device->getOs()->setContains($device->getDeviceUa(), 'Mac_PowerPC', 'Mac OS X')) {
            return;
        }

        if ($device->getOs()->setContains($device->getDeviceUa(), 'CrOS', 'Chrome OS')) {
            return;
        }

        if ($device->getOs()->name) {
            return;
        }

        if (strpos($device->getDeviceUa(), 'FreeBSD') !== false) {
            $device->getOs()->name = 'FreeBSD';

            return;
        }

        if (strpos($device->getDeviceUa(), 'NetBSD') !== false) {
            $device->getOs()->name = 'NetBSD';

            return;
        }

        // Last ditch efforts
        if (strpos($device->getDeviceUa(), 'Linux') !== false || strpos($device->getDeviceUa(), 'X11') !== false) {
            $device->getOs()->name = 'Linux';

            return;
        }
    }

    /**
     * normalize the Browser Information
     *
     * @param \Wurfl\VirtualCapability\Tool\Device $device
     */
    private static function normalizeBrowser(Device $device)
    {
        if ($device->getBrowser()->name === 'IE' && preg_match('#Trident/([\d\.]+)#', $device->getDeviceUa(), $matches)) {
            if (array_key_exists($matches[1], self::$trident_map)) {
                $compatibilityViewCheck = self::$trident_map[$matches[1]];

                if ($device->getBrowser()->version !== $compatibilityViewCheck) {
                    $device->getBrowser()->version = $compatibilityViewCheck . '(Compatibility View)';
                }

                return;
            }
        }
    }
}
