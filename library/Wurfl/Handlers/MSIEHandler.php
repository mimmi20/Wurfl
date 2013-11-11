<?php
namespace Wurfl\Handlers;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    \Wurfl\Handlers
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */

/**
 * MSIEAgentHandler
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class MSIEHandler extends Handler
{

    protected $prefix = "MSIE";

    public static $constantIDs = array(
        'msie',
        'msie_4',
        'msie_5',
        'msie_5_5',
        'msie_6',
        'msie_7',
        'msie_8',
        'msie_9',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }
        if (Utils::checkIfContainsAnyOf($userAgent, array('Opera', 'armv', 'MOTO', 'BREW'))) {
            return false;
        }

        return Utils::checkIfStartsWith($userAgent, 'Mozilla') && Utils::checkIfContains($userAgent, 'MSIE');
    }

    public function applyConclusiveMatch($userAgent)
    {
        $matches = array();
        if (preg_match('/^Mozilla\/4\.0 \(compatible; MSIE (\d)\.(\d);/', $userAgent, $matches)) {
            switch ($matches[1]) {
                // cases are intentionally out of sequence for performance
                case 7:
                    return 'msie_7';
                    break;
                case 8:
                    return 'msie_8';
                    break;
                case 9:
                    return 'msie_9';
                    break;
                case 6:
                    return 'msie_6';
                    break;
                case 4:
                    return 'msie_4';
                    break;
                case 5:
                    return ($matches[2] == 5) ? 'msie_5_5' : 'msie_5';
                    break;
                default:
                    return 'msie';
                    break;
            }
        }
        $tolerance = Utils::firstSlash($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}