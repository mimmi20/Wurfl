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

namespace Wurfl\VirtualCapability\Single;

use Wurfl\Handlers\Utils;
use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 */
class IsAppWebview extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array('device_os');

    /**
     * Simple strings or regex patterns that indicate that the UA is from a built in browser that sends webview style
     * UAs
     *
     * @var array
     */
    private $blacklist = array(
        'com.android.browser',
        'com.htc.sense.browser',
        'com.asus.browser',
        'com.google.android.browser',
        'com.lenovo.browser',
        'com.huawei.android.browser',
    );

    /**
     * Simple strings or regex patterns that indicate that the UA is from a app that sends webview UAs
     *
     * @var array
     */
    private $whitelist = array(
        'com.facebook.katana',
        'com.ksmobile.cb',
        'com.nhn.android.search',
        'app.staples',
        'flipboard.app',
        'com.google.android.apps.magazines',
        'com.pandora.android',
        'com.stumbleupon.android.app',
    );

    /**
     * Simple strings or regex patterns that indicate that the UA is from a third party browser
     *
     * @var array
     */
    private $thirdPartyBrowsers = array(
        'UCBrowser',
        'Opera',
        ' OPR/',
        'YaBrowser',
        'MiuiBrowser',
        'MQQBrowser',
        'CriOS',
        'Firefox',
    );

    /**
     * @return bool
     */
    protected function compute()
    {
        $ua          = $this->request->getUserAgentNormalized();
        $ua_original = $this->request->getUserAgent();

        // ->contains() can take an array
        if (Utils::checkIfContainsAnyOf($ua, $this->thirdPartyBrowsers)) {
            return false;
        }

        // Lollipop implementation of webview adds a ; wv to the UA
        if ($this->device->device_os === 'Android' && false !== strpos($ua_original, '; wv) ')) {
            return true;
        }

        // Handling Chrome separately
        if ($this->device->device_os === 'Android'
            && Utils::checkIfContains($ua, 'Chrome')
            && !Utils::checkIfContains($ua, 'Version')
        ) {
            return false;
        }

        // iOS webview logic is pretty simple
        if ($this->device->device_os === 'iOS' && !Utils::checkIfContains($ua, 'Safari')) {
            return true;
        }

        // So is Mac OS X's webview logic
        if ($this->device->advertised_device_os === 'Mac OS X' && !Utils::checkIfContains($ua, 'Safari')) {
            return true;
        }

        if ($this->device->device_os === 'Android') {
            if ($this->request->originalHeaderExists('HTTP_X_REQUESTED_WITH')) {
                $requested_with = $this->request->getOriginalHeader('HTTP_X_REQUESTED_WITH');

                // The whitelist is an array with X-Requested-With header field values sent by known apps
                if (in_array($requested_with, $this->whitelist)) {
                    return true;
                } elseif (in_array($requested_with, $this->blacklist)) {
                    // The blacklist is an array with X-Requested-With header field values sent by known stock browsers
                    return false;
                }
            }

            // Now we handle Android UAs that haven't been eliminated above (No X-Requested-With header and not a third party browser)
            // Make sure to use the original UA and not the normalized one
            if (preg_match('#Mozilla/5.0 \(Linux;( U;)? Android.*AppleWebKit.*\(KHTML, like Gecko\)#', $ua_original)) {
                // Among those UAs in here, we are interested in UAs from apps that contain a webview style UA and add stuff to the beginning or the end of the string(FB, Flipboard etc.)
                // Android >= 4.4
                if ((strpos($ua, 'Android 4.4') !== false ||
                        strpos($ua, 'Android 5.') !== false) &&
                    !preg_match('#^Mozilla/5.0 \(Linux; Android [45]\.[\d\.]+; .+ Build/.+\) AppleWebKit/[\d\.+]+ \(KHTML, like Gecko\) Version/[\d\.]+ Chrome/([\d]+)\.[\d\.]+? (?:Mobile )?Safari/[\d\.+]+$#', $ua_original)
                ) {
                    if (preg_match('#Chrome/(\d+)\.#', $ua, $matches)) {
                        if ($matches[1] < 30) {
                            return false;
                        }
                    }

                    return true;
                }

                // Android < 4.4
                if (preg_match('#Android [1234]\.[123]#', $ua) &&
                    !preg_match('#^Mozilla/5.0 \(Linux;( U;)? Android [1234]\.[\d\.]+(-update1)?; [a-zA-Z]+-[a-zA-Z]+; .+ Build/.+\) AppleWebKit/[\d\.+]+ \(KHTML, like Gecko\) Version/[\d\.]+ (Mobile )?Safari/[\d\.+]+$#', $ua_original)
                ) {
                    return true;
                }
            }

            return false;
        }

        // Return is_app_webview = false for everything else
        return false;
    }
}
