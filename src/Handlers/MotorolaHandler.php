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

use Wurfl\Constants;

/**
 * MotorolaUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class MotorolaHandler
    extends AbstractHandler
{

    protected $prefix = "MOTOROLA";

    public static $constantIDs = array(
        'mot_mib22_generic',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return (Utils::checkIfStartsWithAnyOf(
                $userAgent,
                array('Mot-', 'MOT-', 'MOTO', 'moto')
            ) || Utils::checkIfContains($userAgent, 'Motorola'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        if (Utils::checkIfStartsWithAnyOf($userAgent, array('Mot-', 'MOT-', 'Motorola'))) {
            return $this->getDeviceIDFromRIS($userAgent, Utils::firstSlash($userAgent));
        }

        return $this->getDeviceIDFromLD($userAgent, 5);
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContainsAnyOf($userAgent, array('MIB/2.2', 'MIB/BER2.2'))) {
            return "mot_mib22_generic";
        }

        return Constants::NO_MATCH;
    }
}
