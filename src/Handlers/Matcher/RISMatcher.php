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

namespace Wurfl\Handlers\Matcher;

/**
 * WURFL Reduction in String user agent matcher.
 * This User Agent Matcher uses its match() method to find a matching user agent by
 * removing characters from the right side of the User Agents one-by-one until either
 * a match is found, or the string length is lower than the specified tolerance.
 *
 * @see        match()
 */
class RISMatcher implements MatcherInterface
{
    /**
     * Instance of \Wurfl\Handlers\Matcher\LDMatcher
     *
     * @var \Wurfl\Handlers\Matcher\LDMatcher
     */
    private static $instance;

    /**
     * Returns an instance of the RISMatcher singleton
     *
     * @return RISMatcher
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns the closest match of $needle in $collection of user agents
     *
     * @param array  &$collection array Array of user agents
     * @param string $needle      string substring to look for in user agent
     * @param int    $tolerance   integer Minimum required length of prefix match
     *
     * @return string Device ID that matches user agent or null if not found
     */
    public function match(&$collection, $needle, $tolerance)
    {
        $match        = null;
        $bestDistance = 0;
        $low          = 0;
        $high         = count($collection) - 1;
        $bestIndex    = 0;

        while ($low <= $high) {
            $mid      = (int) round(($low + $high) / 2);
            $find     = $collection[$mid];
            $distance = $this->longestCommonPrefixLength($needle, $find);

            if ($distance >= $tolerance && $distance > $bestDistance) {
                $bestIndex    = $mid;
                $match        = $find;
                $bestDistance = $distance;
            }

            $cmp = strcmp($find, $needle);
            if ($cmp < 0) {
                $low = $mid + 1;
            } else {
                if ($cmp > 0) {
                    $high = $mid - 1;
                } else {
                    break;
                }
            }
        }

        if ($bestDistance < $tolerance) {
            return;
        }

        if ($bestIndex === 0) {
            return $match;
        }

        return $this->firstOfTheBests($collection, $needle, $bestIndex, $bestDistance);
    }

    /**
     * Returns the most accurate match of $needle in $collection
     *
     * @param $collection   array Array of user agents
     * @param string $needle       string String to search for in user agents
     * @param integer $bestIndex    integer
     * @param integer $bestDistance integer
     *
     * @return string Device ID
     */
    private function firstOfTheBests($collection, $needle, $bestIndex, $bestDistance)
    {
        while ($bestIndex > 0 && $this->longestCommonPrefixLength(
            $collection[$bestIndex - 1],
            $needle
        ) == $bestDistance) {
            $bestIndex--;
        }

        return $collection[$bestIndex];
    }

    /**
     * Returns the largest number of matching characters between $s and $t
     *
     * @param $string1 string String 1
     * @param $string2 string String 2
     *
     * @return int Longest prefix length
     */
    private function longestCommonPrefixLength($string1, $string2)
    {
        $length = min(strlen($string1), strlen($string2));
        $index  = 0;

        while ($index < $length) {
            if ($string1[$index] !== $string2[$index]) {
                break;
            }

            ++$index;
        }

        return $index;
    }
}
