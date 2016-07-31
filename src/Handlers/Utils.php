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

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * WURFL user agent hander utilities
 */
class Utils
{
    /**
     * The worst allowed match tolerance
     *
     * @var int
     */
    const WORST_MATCH = 7;

    /**
     * @var array Collection of mobile browser keywords
     */
    private static $mobileBrowsers = array(
        'midp',
        'mobile',
        'android',
        'samsung',
        'nokia',
        'up.browser',
        'phone',
        'opera mini',
        'opera mobi',
        'brew',
        'sonyericsson',
        'blackberry',
        'netfront',
        'uc browser',
        'symbian',
        'j2me',
        'wap2.',
        'up.link',
        ' arm;',
        'windows ce',
        'vodafone',
        'ucweb',
        'zte-',
        'ipad;',
        'docomo',
        'armv',
        'maemo',
        'palm',
        'bolt',
        'fennec',
        'wireless',
        'adr-',
        // Required for HPM Safari
        'htc',
        // Used to keep Xbox away from the desktop matchers
        '; xbox',
        'nintendo',
        // These keywords keep IE-like mobile UAs out of the MSIE bucket
        // ex: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; XBLWP7;  ZuneWP7)
        'zunewp7',
        'skyfire',
        'silk',
        'untrusted',
        'lgtelecom',
        ' gt-',
        'ventana',
    );

    private static $smartTVBrowsers = array(
        'googletv',
        'boxee',
        'sonydtv',
        'appletv',
        'smarttv',
        'smart-tv',
        'dlna',
        'ce-html',
        'inettvbrowser',
        'opera tv',
        'viera',
        'konfabulator',
        'sony bravia',
        'crkey',
        'sonycebrowser',
        'hbbtv',
        'large screen',
        'netcast',
        'philipstv',
        'digital-tv',
        ' mb90/',
        ' mb91/',
        ' mb95/',
        'vizio-dtv',
        'bravia',
    );

    private static $desktopBrowsers = array(
        'wow64',
        '.net clr',
        'gtb7',
        'macintosh',
        'slcc1',
        'gtb6',
        'funwebproducts',
        'aol 9.',
        'gtb8',
        'iceweasel',
        'epiphany',
    );

    private static $robots = array(
        '+http',
        'bot',
        'crawler',
        'spider',
        'novarra',
        'transcoder',
        'yahoo! searchmonkey',
        'yahoo! slurp',
        'feedfetcher-google',
        'mowser',
        'trove',
        'google web preview',
        'googleimageproxy',
        'mediapartners-google',
        'azureus',
        'inquisitor',
        'baiduspider',
        'baidumobaider',
        'holmes/',
        'libwww-perl',
        'netSprint',
        'yandex',
        'ineturl',
        'jakarta',
        'lorkyll',
        'microsoft url control',
        'indy library',
        'slurp',
        'crawl',
        'wget',
        'ucweblient',
        'snoopy',
        'untrursted',
        'mozfdsilla',
        'ask jeeves',
        'jeeves/teoma',
        'mechanize',
        'http client',
        'servicemonitor',
        'httpunit',
        'hatena',
        'ichiro',
    );

    /**
     * @var bool
     */
    private static $isSmartTv;

    /**
     * @var bool
     */
    private static $isMobileBrowser;

    /**
     * @var bool
     */
    private static $isDesktopBrowser;

    /**
     * @var bool
     */
    private static $isRobot;

    /**
     * Resets cached detection variables for performance
     */
    public static function reset()
    {
        self::$isDesktopBrowser = null;
        self::$isMobileBrowser  = null;
        self::$isSmartTv        = null;
        self::$isRobot          = null;
    }

    /**
     * Alias of \Wurfl\Handlers\Matcher\RISMatcher::match()
     *
     * @param array  $collection
     * @param string $needle
     * @param int    $tolerance
     *
     * @return string Matched user agent
     * @see \Wurfl\Handlers\Matcher\RISMatcher::match()
     */
    public static function risMatch($collection, $needle, $tolerance)
    {
        return Matcher\RISMatcher::getInstance()
            ->match($collection, $needle, $tolerance);
    }

    /**
     * Returns the character position (index) of the $target in $string, starting from a given index.
     * If target is not found, returns length of user agent.
     *
     * @param string $haystack      Haystack to be searched in
     * @param string $needle        Target string to search for
     * @param int    $startingIndex Character postition to start looking for the target
     *
     * @return int Character position (index) or full length
     */
    public static function indexOf($haystack, $needle, $startingIndex = 0)
    {
        return strpos($haystack, $needle, $startingIndex);
    }

    /**
     * Returns the character position (index) of the $target in $string, starting from a given index.
     * If target is not found, returns length of user agent.
     *
     * @param string $haystack      Haystack to be searched in
     * @param string $needle        Target string to search for
     * @param int    $startingIndex Character postition to start looking for the target
     *
     * @return int Character position (index) or full length
     */
    public static function indexOfOrLength($haystack, $needle, $startingIndex = 0)
    {
        $length = strlen($haystack);

        if ($startingIndex === false || $startingIndex > $length) {
            return $length;
        }

        $pos = strpos($haystack, $needle, $startingIndex);

        return ($pos === false) ? $length : $pos;
    }

    /**
     * Lowest char index of the first instance of any of the $needles found in $userAgent, starting at $startIndex;
     * if no match is found, the length of the string is returned
     *
     * @param string $userAgent     haystack
     * @param array  $needles       Array of (string)needles to search for in $userAgent
     * @param int    $startingIndex Char index for start of search
     *
     * @return int Char index of left-most match or length of string
     */
    public static function indexOfAnyOrLength($userAgent, $needles = array(), $startingIndex = 0)
    {
        $length = strlen($userAgent);

        if (count($needles) === 0) {
            return $length;
        }

        if ($startingIndex === false || $startingIndex > $length) {
            return $length;
        }

        $min = $length;
        foreach ($needles as $needle) {
            $index = self::indexOfOrLength($userAgent, $needle, $startingIndex);
            if ($index < $min) {
                $min = $index;
            }
        }

        return $min;
    }

    /**
     * Returns true if the give $userAgent is from a mobile device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isMobileBrowser($userAgent)
    {
        $userAgent_lower = strtolower($userAgent);
        if (self::$isMobileBrowser === null) {
            if (self::isDesktopBrowser($userAgent)) {
                self::$isMobileBrowser = false;
            } elseif (self::checkIfContainsAnyOf($userAgent_lower, self::$mobileBrowsers)) {
                self::$isMobileBrowser = true;
            } elseif (self::regexContains($userAgent, '/[^\d]\d{3}x\d{3}/')) {
                self::$isMobileBrowser = true;
            } else {
                self::$isMobileBrowser = false;
            }
        }

        return self::$isMobileBrowser;
    }

    /**
     * Returns true if the give $userAgent is from a desktop device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isDesktopBrowser($userAgent)
    {
        if (self::$isDesktopBrowser !== null) {
            return self::$isDesktopBrowser;
        }

        self::$isDesktopBrowser = false;
        $userAgent              = strtolower($userAgent);

        foreach (self::$desktopBrowsers as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isDesktopBrowser = true;
                break;
            }
        }

        return self::$isDesktopBrowser;
    }

    /**
     * Returns true if the give $userAgent is from a robot
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isRobot($userAgent)
    {
        self::$isRobot = false;
        $userAgent     = strtolower($userAgent);

        foreach (self::$robots as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isRobot = true;
                break;
            }
        }

        return self::$isRobot;
    }

    /**
     * Is the given user agent very likely to be a desktop browser
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isDesktopBrowserHeavyDutyAnalysis($userAgent)
    {
        // Check Smart TV keywords
        if (self::isSmartTV($userAgent)) {
            return false;
        }

        //WP Desktop - Edge Mode
        if (self::checkIfContainsAll($userAgent, array('Mozilla/5.0 (Windows NT ', ' ARM;', ' Edge/'))) {
            return false;
        }

        // Chrome
        if (self::checkIfContains($userAgent, 'Chrome') && !self::checkIfContainsAnyOf(
                $userAgent,
                array('Android', 'Ventana')
            )
        ) {
            return true;
        }

        // Check mobile keywords
        if (self::isMobileBrowser($userAgent)) {
            return false;
        }

        if (self::checkIfContains(
            $userAgent,
            'PPC'
        )
        ) {
            return false;
        } // PowerPC; not always mobile, but we'll kick it out

        // Firefox;  fennec is already handled in the \Wurfl\Constants::$MOBILE_BROWSERS keywords
        if (self::checkIfContains($userAgent, 'Firefox') && !self::checkIfContains($userAgent, 'Tablet')) {
            return true;
        }

        // Safari
        if (preg_match(
            '#^Mozilla/5\.0 \((?:Macintosh|Windows)[^\)]+\) AppleWebKit/[\d\.]+ \(KHTML, like Gecko\) Version/[\d\.]+ ' . 'Safari/[\d\.]+$#',
            $userAgent
        )) {
            return true;
        }

        // Opera Desktop
        if (self::checkIfStartsWithAnyOf($userAgent, array('Opera/9.80 (Windows NT', 'Opera/9.80 (Macintosh'))) {
            return true;
        }

        // Check desktop keywords
        if (self::isDesktopBrowser($userAgent)) {
            return true;
        }

        // Internet Explorer 11
        if (preg_match('/^Mozilla\/5\.0 \(Windows NT.+?Trident.+?; rv:\d\d\.\d+\)/', $userAgent)) {
            return true;
        }

        // Internet Explorer 9 or 10
        if (preg_match('/^Mozilla\/5\.0 \(compatible; MSIE (9|10)\.0; Windows NT \d\.\d/', $userAgent)) {
            return true;
        }

        // Internet Explorer <9
        if (preg_match('/^Mozilla\/4\.0 \(compatible; MSIE \d\.\d; Windows NT \d\.\d/', $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the give $userAgent is from a mobile device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isSmartTV($userAgent)
    {
        if (self::$isSmartTv !== null) {
            return self::$isSmartTv;
        }
        self::$isSmartTv = false;
        $userAgent       = strtolower($userAgent);
        foreach (self::$smartTVBrowsers as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isSmartTv = true;
                break;
            }
        }

        return self::$isSmartTv;
    }

    /**
     * Returns true if the give $userAgent is from a spam bot or crawler
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isSpamOrCrawler($userAgent)
    {
        return self::checkIfContainsAnyOf($userAgent, array('Spam', 'FunWebProducts'));
    }

    /**
     * Returns the position of third occurrence of a ;(semi-column) if it exists
     * or the string length if no match is found
     *
     * @param string $haystack
     *
     * @return int Char index of third semicolon or length
     */
    public static function thirdSemiColumn($haystack)
    {
        $thirdSemiColumnIndex = self::ordinalIndexOf($haystack, ';', 3);
        if ($thirdSemiColumnIndex < 0) {
            return strlen($haystack);
        }

        return $thirdSemiColumnIndex;
    }

    /**
     * The nth($ordinal) occurance of $needle in $haystack or -1 if no match is found
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $ordinal
     *
     * @throws \InvalidArgumentException
     * @return int                       Char index of occurance
     */
    public static function ordinalIndexOf($haystack, $needle, $ordinal)
    {
        if (is_null($haystack) || empty($haystack)) {
            throw new \InvalidArgumentException('haystack must not be null or empty');
        }

        if (!is_integer($ordinal)) {
            throw new \InvalidArgumentException('ordinal must be a positive ineger');
        }

        $found = 0;
        $index = -1;
        do {
            $index = strpos($haystack, $needle, $index + 1);
            $index = is_int($index) ? $index : -1;
            if ($index < 0) {
                return $index;
            }
            ++$found;
        } while ($found < $ordinal);

        return $index;
    }

    /**
     * First occurance of a / character
     *
     * @param string $string Haystack
     *
     * @return null|int Character position
     */
    public static function firstSlash($string)
    {
        return self::findCharPosition($string, '/');
    }

    /**
     * Second occurance of a / character
     *
     * @param string $string Haystack
     *
     * @return null|int Character position
     */
    public static function secondSlash($string)
    {
        return self::findCharPosition($string, '/', self::findCharPosition($string, '/'));
    }

    /**
     * Number of slashes ('/')
     *
     * @param $string
     *
     * @return int Count
     */
    public static function numSlashes($string)
    {
        return substr_count($string, '/');
    }

    /**
     * First occurance of a space character
     *
     * @param string $string Haystack
     *
     * @return null|int Character position
     */
    public static function firstSpace($string)
    {
        return self::findCharPosition($string, ' ');
    }

    /**
     * The character position of the first open parenthesis.  If there are no open parenthesis, returns null
     *
     * @param string $string Haystack
     *
     * @return null|int Character position
     */
    public static function firstOpenParen($string)
    {
        return self::findCharPosition($string, '(');
    }

    /**
     * The tolerance position of $char in $string.  If there are no occurrences of $char, returns null
     *
     * @param string $string Haystack
     *
     * @param        $char
     *
     * @return int Character position
     */
    public static function firstChar($string, $char)
    {
        return self::findCharPosition($string, $char);
    }

    /**
     * First occurance of a ; character or length
     *
     * @param string $string Haystack
     *
     * @return int Char index
     */
    public static function firstSemiColonOrLength($string)
    {
        return self::firstMatchOrLength($string, ';');
    }

    /**
     * First occurance of $toMatch string or length
     *
     * @param string $string  Haystack
     * @param string $toMatch Needle
     *
     * @return int Char index
     */
    public static function firstMatchOrLength($string, $toMatch)
    {
        $firstMatch = strpos($string, $toMatch);

        return ($firstMatch === false) ? strlen($string) : $firstMatch;
    }

    /**
     * Returns true if $haystack contains $needle
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfContains($haystack, $needle)
    {
        return (strpos($haystack, $needle) !== false);
    }

    /**
     * Returns true if $haystack contains any of the (string)needles in $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfContainsAnyOf($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (self::checkIfContains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if $haystack contains all of the (string)needles in $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfContainsAll($haystack, $needles = array())
    {
        foreach ($needles as $needle) {
            if (!self::checkIfContains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if $haystack contains $needle without regard for case
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfContainsCaseInsensitive($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }

    /**
     * Returns true if $haystack starts with $needle
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfStartsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Returns true if $haystack starts with $needle
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfStartsWithCaseInsensitive($haystack, $needle)
    {
        return stripos($haystack, $needle) === 0;
    }

    /**
     * Returns true if $haystack starts with any of the $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfStartsWithAnyOf($haystack, $needles)
    {
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                if (strpos($haystack, $needle) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the string position of the end of the RIS delimiter, or false if there is no RIS delimiter
     *
     * @param string $userAgent
     *
     * @return int|bool
     */
    public static function toleranceToRisDelimeter($userAgent)
    {
        $tolerance = strpos($userAgent, WurflConstants::RIS_DELIMITER);
        if ($tolerance === false) {
            return false;
        }

        // Push the tolerance to the *end* of the RIS Delimiter
        return $tolerance + strlen(WurflConstants::RIS_DELIMITER);
    }

    /**
     * Removes the locale portion from the userAgent
     *
     * @param string $userAgent
     *
     * @return string User agent without language string
     */
    public static function removeLocale($userAgent)
    {
        return preg_replace('/; ?[a-z]{2}(?:-r?[a-zA-Z]{2})?(?:\.utf8|\.big5)?\b-?(?!:)/', '; xx-xx', $userAgent);
    }

    /**
     * The character position of the first close parenthesis.  If there are no close parenthesis, returns null
     *
     * @param string $string Haystack
     *
     * @return null|int Character position
     */
    public static function firstCloseParen($string)
    {
        return self::findCharPosition($string, ')');
    }

    /**
     * The character position in a string.  If not present, returns null
     *
     * @param string $string
     * @param string $char
     * @param int    $startAt
     *
     * @return null|int Character position
     */
    public static function findCharPosition($string, $char, $startAt = 0)
    {
        $position = strpos($string, $char, $startAt);

        return ($position !== false) ? $position + 1 : null;
    }

    /**
     * Check if value contains another string using PCRE (Perl Compatible Reqular Expressions)
     *
     * @param               $string
     * @param  string|array $find Target regex string or array of regex strings
     *
     * @return bool
     */
    public static function regexContains($string, $find)
    {
        if (is_array($find)) {
            foreach ($find as $part) {
                if (preg_match($part, $string)) {
                    return true;
                }
            }

            return false;
        } else {
            return (preg_match($find, $string));
        }
    }

    /**
     * Get the Android version from the User Agent, or the default Android version is it cannot be determined
     *
     * @param  string $userAgent  User Agent
     * @param  bool   $useDefault Return the default version on fail, else return null
     *
     * @return null|string Android version
     * @see self::ANDROID_DEFAULT_VERSION
     */
    public static function getAndroidVersion($userAgent, $useDefault = true)
    {
        $releaseMap = [
            'Cupcake'            => '1.5',
            'Donut'              => '1.6',
            'Eclair'             => '2.1',
            'Froyo'              => '2.2',
            'Gingerbread'        => '2.3',
            'Honeycomb'          => '3.0',
            'Ice Cream Sandwich' => '4.0',
            'Jelly Bean'         => '4.1', // Note: 4.2/4.3 is also Jelly Bean
            'KitKat'             => '4.4',
        ];

        // Replace Android version names with their numbers
        // ex: Froyo => 2.2
        $userAgent = str_replace(
            array_keys($releaseMap),
            array_values($releaseMap),
            $userAgent
        );

        // Initializing $version
        $version = null;

        // Look for "Android <Version>" first and then for "Android/<Version>"
        if (preg_match('#Android (\d\.\d)#', $userAgent, $matches)) {
            $version = $matches[1];
        } elseif (preg_match('#Android/(\d\.\d)#', $userAgent, $matches)) {
            $version = $matches[1];
        }

        $validVersions = [
            '1.0',
            '1.5',
            '1.6',
            '2.0',
            '2.1',
            '2.2',
            '2.3',
            '2.4',
            '3.0',
            '3.1',
            '3.2',
            '3.3',
            '4.0',
            '4.1',
            '4.2',
            '4.3',
            '4.4',
            '4.5',
            '5.0',
            '5.1',
            '5.2',
            '5.3',
            '6.0',
            '6.1',
            '7.0',
        ];

        // Now check extracted Android version for validity
        if (null !== $version && in_array($version, $validVersions)) {
            return $version;
        }

        return $useDefault ? '2.0' : null;
    }

    /**
     * Get the model name from the provided user agent or null if it cannot be determined
     *
     * @param  string $userAgent
     *
     * @return null|string
     */
    public static function getAndroidModel($userAgent)
    {
        // Normalize spaces in UA before capturing parts
        $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);

        // Logic to detect some Gionee UAs like: (must remain above the regular model name extracting regex)
        // Mozilla/5.0 (Linux; U; Android 4.2.2; zh-cn; Build/JDQ39 ) AppleWebKit/534.30 (KHTML,like Gecko) Version/4.2.2 Mobile Safari/534.30 GiONEE-GN9000/GN9000 RV/4.2.8 GNBR/5.0.0.v Id/0470FB91EE7E5465B21531B855F06353
        if (preg_match('#Mobile Safari/[\d\.]+ (GiONEE-[A-Za-z0-9]+)/#', $userAgent, $matches)) {
            $model = $matches[1];
            // Different logic for Mozillite and non-Mozillite UAs to isolate model name
            // Non-Mozillite UAs get first preference
        } elseif (preg_match(
            '#(^[A-Za-z0-9_\-\+ ]+)[/ ]?(?:[A-Za-z0-9_\-\+\.]+)? +Linux/[0-9\.\+]+ +Android[ /][0-9\.]+ +Release/[0-9\.]+#',
            $userAgent,
            $matches
        )) {
            // Trim off spaces and semicolons
            $model = rtrim($matches[1], ' ;');
            // Another kind of Non-Mozillite UA. Seperate regex for better readability
        } elseif (preg_match(
            '#(^[A-Za-z0-9_\-\+ ]+)[/ ]?(?:[A-Za-z0-9_\-\+\.]+)? Android/[0-9\.]+ \(Linux;#',
            $userAgent,
            $matches
        )) {
            // Trim off spaces and semicolons
            $model = rtrim($matches[1], ' ;');
            // Locales are optional for matching model name since UAs like Chrome Mobile do not contain them
        } elseif (preg_match('#Android [^;]+;(?>(?: xx-xx[ ;]+)?)(.+?)(?:Build/|MIUI/|\))#', $userAgent, $matches)) {
            // Trim off spaces and semicolons
            $model = rtrim($matches[1], ' ;');
            // Additional logic to capture model names in Amazon webview/appstore UAs
        } elseif (preg_match(
            '#^(?:AmazonWebView|Appstore|Amazon\.com)/.+Android[/ ][\d\.]+/(?:[\d]+/)?([A-Za-z0-9_\- ]+)\b#',
            $userAgent,
            $matches
        )) {
            $model = $matches[1];
        } else {
            return null;
        }

        // The previous RegEx may return just "Build/.*" for UAs like:
        // HTC_Dream Mozilla/5.0 (Linux; U; Android 1.5; xx-xx; Build/CUPCAKE) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1
        if (strpos($model, 'Build/') === 0) {
            return null;
        }

        // Replace xx-xx (locale) in the model name with ''
        $model = str_replace('xx-xx', '', $model);

        // Normalize Chinese UAs
        $model = preg_replace('#(?:_CMCC_TD|_CMCC|_TD)\b#', '', $model);

        // Normalize models with resolution
        if (strpos($model, '*') !== false) {
            if (($pos = strpos($model, '/')) !== false) {
                $model = substr($model, 0, $pos);
            }
        }

        // Normalize Huawei UAs
        $model = str_replace('HW-HUAWEI_', 'HUAWEI ', $model);

        // Normalize Coolpad UAs
        if (strpos($model, 'YL-Coolpad') !== false) {
            $model = preg_replace('#YL-Coolpad[ _]#', 'Coolpad ', $model);
        }

        // HTC
        if (strpos($model, 'HTC') !== false) {
            // Normalize "HTC/"
            $model = preg_replace('#HTC[ _\-/]#', 'HTC~', $model);

            // Remove the version
            if (($pos = strpos($model, '/')) !== false) {
                $model = substr($model, 0, $pos);
            }
            $model = preg_replace('#( V| )\d+?\.[\d\.]+$#', '', $model);
        }

        // Samsung
        $model = preg_replace('#(SAMSUNG[^/]+)/.*$#', '$1', $model);

        // Orange
        $model = preg_replace('#ORANGE/.*$#', 'ORANGE', $model);

        // LG
        $model = preg_replace('#(LG-?[A-Za-z0-9\-]+).*$#', '$1', $model);

        // Serial Number
        $model = preg_replace('#\[[\d]{10}\]#', '', $model);

        // Remove whitespace
        $model = trim($model);

        // Normalize Samsung and Sony/SonyEricsson model name changes due to Chrome Mobile
        $model = preg_replace('#^(?:SAMSUNG|SonyEricsson|Sony)[ \-]?#', '', $model);

        return (strlen($model) === 0) ? null : $model;
    }
}
