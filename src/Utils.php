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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl;

/**
 * WURFL related utilities
 *
 * @package    WURFL
 */
class Utils
{
    /**
     * Returns true if given $deviceID is the 'generic' WURFL device
     *
     * @param string $deviceID
     *
     * @return bool
     */
    public static function isGeneric($deviceID)
    {
        if (strcmp($deviceID, WurflConstants::GENERIC) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Recursively merges $array1 with $array2, returning the result
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function arrayMergeRecursiveUnique($array1, $array2)
    {
        // LOOP THROUGH $array2
        foreach ($array2 as $k => $v) {

            // CHECK IF VALUE EXISTS IN $array1
            if (!empty($array1[$k])) {
                // IF VALUE EXISTS CHECK IF IT'S AN ARRAY OR A STRING
                if (!is_array($array2[$k])) {
                    // OVERWRITE IF IT'S A STRING
                    $array1[$k] = $array2[$k];
                } else {
                    // RECURSE IF IT'S AN ARRAY
                    $array1[$k] = self::arrayMergeRecursiveUnique($array1[$k], $array2[$k]);
                }
            } else {
                // IF VALUE DOESN'T EXIST IN $array1 USE $array2 VALUE
                $array1[$k] = $v;
            }
        }
        unset($k, $v);

        return $array1;
    }
}
