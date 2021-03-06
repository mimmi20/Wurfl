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

namespace Wurfl\VirtualCapability\Single;

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
    );

    /**
     * @return bool
     */
    protected function compute()
    {
        $userAgent = $this->request->getUserAgent();

        if ($this->device->getCapability('device_os') === 'iOS' && !Utils::checkIfContains($userAgent, 'Safari')) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if ($pattern[0] === '#') {
                // Regex
                if (preg_match($pattern, $userAgent)) {
                    return true;
                }
                continue;
            }

            // Substring matches are not abstracted for performance
            $patternLength   = strlen($pattern);
            $userAgentLength = strlen($userAgent);

            if ($pattern[0] === '^') {
                // Starts with
                if (strpos($userAgent, substr($pattern, 1)) === 0) {
                    return true;
                }
            } elseif ($pattern[$patternLength - 1] === '$') {
                // Ends with
                --$patternLength;
                $pattern = substr($pattern, 0, $patternLength);

                if (strpos($userAgent, $pattern) === ($userAgentLength - $patternLength)) {
                    return true;
                }
            } else {
                // Match anywhere
                if (strpos($userAgent, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
