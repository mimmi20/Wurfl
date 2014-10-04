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

use Wurfl\Constants;

/**
 * HTCMacUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class HTCMacHandler
    extends AbstractHandler
{

    protected $prefix = "HTCMAC";

    public static $constantIDs = array(
        'generic_android_htc_disguised_as_mac',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfStartsWith($userAgent, 'Mozilla/5.0 (Macintosh') && Utils::checkIfContains(
            $userAgent,
            'HTC'
        );
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $delimiterIndex = strpos($userAgent, Constants::RIS_DELIMITER);

        if ($delimiterIndex !== false) {
            $tolerance = $delimiterIndex + strlen(Constants::RIS_DELIMITER);

            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        return Constants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        return 'generic_android_htc_disguised_as_mac';
    }

    /**
     * @param $userAgent
     *
     * @return mixed|null
     */
    public static function getHTCMacModel($userAgent)
    {
        if (preg_match('/(HTC[^;\)]+)/', $userAgent, $matches)) {
            $model = preg_replace('#[ _\-/]#', '~', $matches[1]);

            return $model;
        }

        return null;
    }
}
