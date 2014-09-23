<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
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

/**
 * NintendoUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NintendoHandler
    extends AbstractHandler
{

    protected $prefix = "NINTENDO";

    public static $constantIDs = array(
        'nintendo_wii_ver1',
        'nintendo_dsi_ver1',
        'nintendo_ds_ver1',
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

        if (Utils::checkIfContains($userAgent, 'Nintendo')) {
            return true;
        }

        return Utils::checkIfStartsWith($userAgent, 'Mozilla/') && Utils::checkIfContainsAll(
            $userAgent,
            array('Nitro', 'Opera')
        );
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        return $this->getDeviceIDFromLD($userAgent);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Nintendo Wii')) {
            return 'nintendo_wii_ver1';
        }

        if (Utils::checkIfContains($userAgent, 'Nintendo DSi')) {
            return 'nintendo_dsi_ver1';
        }

        if ((Utils::checkIfStartsWith($userAgent, 'Mozilla/') && Utils::checkIfContainsAll(
                $userAgent,
                array('Nitro', 'Opera')
            ))
        ) {
            return 'nintendo_ds_ver1';
        }

        return 'nintendo_wii_ver1';
    }
}
