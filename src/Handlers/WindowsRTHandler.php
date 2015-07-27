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

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * WindowsRTUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WindowsRTHandler
    extends AbstractHandler
{
    protected $prefix = 'WINDOWSRT';

    public static $constantIDs = array(
        'generic_windows_8_rt',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContainsAll($userAgent, array('Windows NT ', ' ARM;', 'Trident/'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        // Example Windows 8 RT MSIE 10 UA:
        // Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; ARM; Trident/6.0; Touch)
        //                                                        ^ RIS Tolerance
        //Example Windows 8.1 RT MSIE 11 UA
        //Mozilla/5.0 (Windows NT 6.3; ARM; Trident/7.0; Touch; rv:11.0) like Gecko
        //																    	  ^ RIS Tolerance

        if (Utils::checkIfContainsAll($userAgent, array('like Gecko'))) {
            //Use this logic for MSIE 11 and above
            $search = ' Gecko';
            $idx    = strpos($userAgent, $search);
            if ($idx !== false) {
                // Match to the end of the search string
                return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search));
            }
        } else {
            $search = ' ARM;';
            $idx    = strpos($userAgent, $search);
            if ($idx !== false) {
                // Match to the end of the search string
                return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search));
            }
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContainsAll($userAgent, array('like Gecko'))) {
            return 'windows_8_rt_ver1_subos81';
        } else {
            return 'generic_windows_8_rt';
        }
    }
}
