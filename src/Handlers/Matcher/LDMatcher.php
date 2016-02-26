<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Matcher;

/**
 * WURFL Levenshtein distance user agent matcher.
 * This User Agent Matcher uses the Levenshtein Distance algorithm to determine the
 * distance between to User Agents.  A tolerance is specified on the match() method
 * which limits the distance.  User Agents that match less than or equal to this
 * tolerance are consider to be a match;
 *
 * @link       http://en.wikipedia.org/wiki/Levenshtein_distance
 * @link       http://www.php.net/manual/en/function.levenshtein.php
 * @see        match()
 */
class LDMatcher implements MatcherInterface
{
    /**
     * Instance of \Wurfl\Handlers\Matcher\LDMatcher
     *
     * @var \Wurfl\Handlers\Matcher\LDMatcher
     */
    private static $instance;

    /**
     * Returns an instance of the LDMatcher singleton
     *
     * @return LDMatcher
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param array  &$collection
     * @param string $needle
     * @param int    $tolerance
     *
     * @return string
     */
    public function match(&$collection, $needle, $tolerance)
    {
        $best        = $tolerance;
        $match       = '';
        $needleChars = count_chars($needle);

        foreach ($collection as $userAgent) {
            $uaChars    = count_chars($userAgent);
            $sum        = 0;
            $canApplyId = true;

            //Check from 32 (space) to 122 ('z')
            for ($i = 32; $i < 122; ++$i) {
                $sum += abs($needleChars[$i] - $uaChars[$i]);
                if ($sum > 2 * $tolerance) {
                    $canApplyId = false;
                    break;
                }
            }

            if ($canApplyId === true) {
                $current = levenshtein($needle, $userAgent);
                if ($current <= $best) {
                    $best  = $current - 1;
                    $match = $userAgent;
                }
            }
        }

        return $match;
    }
}
