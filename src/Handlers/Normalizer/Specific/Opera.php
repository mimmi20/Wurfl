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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\Utils;
use Wurfl\Handlers\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 * Return the safari user agent stripping out
 *     - all the chararcters between U; and Safari/xxx
 *
 *  e.g Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_4_11; fr) ... Version/3.1.1 Safari/525.18
 *         becomes
 *         Mozilla/5.0 (Macintosh Safari/525
 *
 * @package    \Wurfl\Handlers\Normalizer\Specific
 */
class Opera
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        // Repair Opera user agents using fake version 9.80
        // Normalize: Opera/9.80 (X11; Linux x86_64; U; sv) Presto/2.9.168 Version/11.50
        // Into: Opera/11.50 (X11; Linux x86_64; U; sv) Presto/2.9.168 Version/11.50
        if (Utils::checkIfStartsWith($userAgent, 'Opera/9.80')) {
            if (preg_match('#Version/(\d+\.\d+)#', $userAgent, $matches)) {
                $userAgent = str_replace('Opera/9.80', 'Opera/' . $matches[1], $userAgent);
            }

            //Match to the '.' in the Opera version number
            return $userAgent;
        }
        //Normalize Opera v15 and above UAs, that say OPR, into 'Opera/version UA' format used above
        if (preg_match('#OPR/(\d+\.\d+)#', $userAgent, $matches)) {
            $prefix    = 'Opera/' . $matches[1] . ' ';
            $userAgent = $prefix . $userAgent;
        }

        return $userAgent;
    }
}
