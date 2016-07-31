<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Capability;

use Wurfl\Handlers\Utils;
use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 */
class IsApp extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array('device_os');

    /**
     * Simple strings or regex patterns that indicate a UA is from a native app
     *
     * @var array
     */
    protected $patterns = array(
        '^Dalvik',
        'Darwin/',
        'CFNetwork',
        '^Windows Phone Ad Client',
        '^NativeHost',
        '^AndroidDownloadManager',
        '-HttpClient',
        '^AppCake',
        'AppEngine-Google',
        'AppleCoreMedia',
        '^AppTrailers',
        '^ChoiceFM',
        '^ClassicFM',
        '^Clipfish',
        '^FaceFighter',
        '^Flixster',
        '^Gold/',
        '^GoogleAnalytics/',
        '^Heart/',
        '^iBrowser/',
        'iTunes-',
        '^Java/',
        '^LBC/3.',
        'Twitter',
        'Pinterest',
        '^Instagram',
        'FBAN',
        '#iP(hone|od|ad)[\d],[\d]#',
        // namespace notation (com.google.youtube)
        '#[a-z]{3,}(?:\.[a-z]+){2,}#',
        //Windows MSIE Webview
        'WebView',
        'FB_IAB',
        'FB4A',
        'MobileApp',
        'DesktopApp',
    );

    /**
     * Simple strings or regex patterns that indicate that the UA is from a app that sends webview UAs
     * @var array
     */
    protected $whitelist = [
        'com.facebook.katana',
        'com.ksmobile.cb',
        'com.nhn.android.search',
        'app.staples',
        'flipboard.app',
        'com.google.android.apps.magazines',
        'com.pandora.android',
        'com.stumbleupon.android.app',
    ];

    /**
     * Simple strings or regex patterns that indicate that the UA is from a built in browser that sends webview style UAs
     * @var array
     */
    protected $blacklist = [];

    /**
     * Simple strings or regex patterns that indicate that the UA is from a third party browser
     * @var array
     */
    protected $third_party_browsers = [
        'UCBrowser',
        'Opera',
        ' OPR/',
        'YaBrowser',
        'MiuiBrowser',
        'MQQBrowser',
        'CriOS',
        'Firefox',
    ];

    /**
     * @return bool
     */
    protected function compute()
    {
        $userAgent = $this->request->getUserAgent();

        // We don't consider browsers apps
        if ($this->isThirdPartyBrowser()) {
            return false;
        }

        if ($this->isAndroidLollipopWebView()) {
            return true;
        }

        if ($this->isiOSWebView()) {
            return true;
        }

        if ($this->isMacOSXWebView()) {
            return true;
        }

        if ($this->isAndroidWebView()) {
            return true;
        }

        $original = $userAgent;

        foreach ($this->patterns as $pattern) {
            if ($pattern[0] === '#') {
                // Regex
                if (Utils::regexContains($original, $pattern)) {
                    return true;
                }
                continue;
            }

            // Substring matches are not abstracted for performance
            $pattern_len = strlen($pattern);
            $user_agent_len = strlen($original);

            if ($pattern[0] === '^') {
                // Starts with
                if (Utils::checkIfStartsWith($original, substr($pattern, 1))) {
                    return true;
                }
                continue;
            }

            if ($pattern[$pattern_len - 1] === '$') {
                // Ends with
                $pattern_len--;
                $pattern = substr($pattern, 0, $pattern_len);
                if (Utils::indexOf($original, $pattern) === ($user_agent_len - $pattern_len)) {
                    return true;
                }
                continue;
            }

            // Match anywhere
            if (Utils::checkIfContains($original, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isThirdPartyBrowser()
    {
        return Utils::checkIfContains($this->request->getUserAgentNormalized(), $this->third_party_browsers);
    }

    /**
     * @return bool
     */
    protected function isAndroidLollipopWebView()
    {
        $userAgent = $this->request->getUserAgent();

        // Lollipop and above implementation of webview adds a ; wv to the UA. Webviews are automatically apps
        // This logic could have been added to the patterns array above but I added it here to maintain consistency with is_app_webview
        return ($this->device->getCapability('device_os') == "Android" && Utils::checkIfContains($userAgent, '; wv) '));
    }

    /**
     * @return bool
     */
    protected function isiOSWebView()
    {
        // iOS webview logic is pretty simple
        return ($this->device->getCapability('device_os') == "iOS" && !Utils::checkIfContains($this->request->getUserAgentNormalized(), 'Safari'));
    }

    /**
     * @return bool
     */
    protected function isMacOSXWebView()
    {
        $userAgent = $this->request->getUserAgent();

        // So is Mac OS X's webview logic
        return Utils::regexContains($userAgent, '#^Mozilla.+(?:PPC|Intel) Mac OS X [0-9\._]+#')
        && !Utils::checkIfContains($this->request->getUserAgentNormalized(), "Safari");
    }

    /**
     * @return bool
     */
    protected function isAndroidWebView()
    {
        if ($this->device->getCapability('device_os') != "Android") {
            return false;
        }

        if ($this->request->originalHeaderExists("HTTP_X_REQUESTED_WITH")) {
            $requested_with = $this->request->getOriginalHeader("HTTP_X_REQUESTED_WITH");

            // The whitelist is an array with X-Requested-With header field values sent by known apps
            if (in_array($requested_with, $this->whitelist)) {
                return true;
            }

            // The blacklist is an array with X-Requested-With header field values sent by known stock browsers
            if (in_array($requested_with, $this->blacklist)) {
                return false;
            }
        }

        $userAgent = $this->request->getUserAgent();
        $original = $userAgent;
        $normalized = $this->request->getUserAgentNormalized();

        // Now we handle Android UAs that haven't been eliminated above (No X-Requested-With header and not a third party browser)
        // Make sure to use the original UA and not the normalized one
        if (!Utils::regexContains($original, '#Mozilla/5.0 \(Linux;( U;)? Android.*AppleWebKit.*\(KHTML, like Gecko\)#')) {
            return false;
        }

        // Among those UAs in here, we are interested in UAs from apps that contain a webview style UA
        // and add stuff to the beginning or the end of the string(FB, Flipboard etc.)

        // Android >= 4.4
        if (Utils::checkIfContains($normalized, ['Android 4.4', 'Android 5.'])
            && !Utils::regexContains($original, '#^Mozilla/5.0 \(Linux; Android [45]\.[\d\.]+; .+ Build/.+\) AppleWebKit/[\d\.+]+ \(KHTML, like Gecko\) Version/[\d\.]+ Chrome/([\d]+)\.[\d\.]+? (?:Mobile )?Safari/[\d\.+]+$#')
        ) {
            $matches = Utils::regexContains($normalized, '#Chrome/(\d+)\.#');

            if ($matches && $matches[1] < 30) {
                return false;
            }

            return true;
        }

        // Android < 4.4
        if (Utils::regexContains($normalized, '#Android [1234]\.[123]#')
            && !Utils::regexContains($original, '#^Mozilla/5.0 \(Linux;( U;)? Android [1234]\.[\d\.]+(-update1)?; [a-zA-Z]+-[a-zA-Z]+; .+ Build/.+\) AppleWebKit/[\d\.+]+ \(KHTML, like Gecko\) Version/[\d\.]+ (Mobile )?Safari/[\d\.+]+$#')
        ) {
            return true;
        }

        // Return is_app_webview = false for everything else
        return false;
    }
}
