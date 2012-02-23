<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Handlers;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */

/**
 * CatchAllUserAgentHanlder
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */

class CatchAllHandler extends Handler
{
    protected $prefix = 'CATCH_ALL';
    const MOZILLA_TOLLERACE = 4;
    
    const MOZILLA5 = 'CATCH_ALL_MOZILLA5';
    const MOZILLA4 = 'CATCH_ALL_MOZILLA4';
    
    private $mozilla4UserAgentsWithDeviceID = array();
    private $mozilla5UserAgentsWithDeviceID = array();
    
    public function __construct($wurflContext, $userAgentNormalizer = null)
    {
        parent::__construct($wurflContext, $userAgentNormalizer);
    }
    
    /**
     * Final Interceptor: Intercept
     * Everything that has not been trapped by a previous handler
     *
     * @param string $userAgent
     * @return boolean always true
     */
    public function canHandle($userAgent)
    {
        return true;
    }
    
    /**
     * If UA starts with Mozilla, apply LD with tollerance 5.
     * If UA does not start with Mozilla, apply RIS on FS
     *
     * @param string $userAgent
     * @return string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $deviceId = \Wurfl\Constants::GENERIC;
        if (Utils::checkIfStartsWith($userAgent, 'Mozilla')) {
            $deviceId = $this->applyMozillaConclusiveMatch($userAgent);
        } else {
            $tollerance = Utils::firstSlash($userAgent);
            $match = Utils::risMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tollerance);
            
            if (!empty($this->userAgentsWithDeviceID[$match])) {
                $deviceId = $this->userAgentsWithDeviceID[$match];
            }
        }
        
        return $deviceId;
    }
    
    private function applyMozillaConclusiveMatch($userAgent)
    {
        if ($this->isMozilla5($userAgent)) {
            return $this->applyMozilla5ConclusiveMatch($userAgent);
        }
        
        if ($this->isMozilla4($userAgent)) {
            return $this->applyMozilla4ConclusiveMatch($userAgent);
        }
        
        $this->logger->log('Applying Catch All Conclusive Match for ua: $userAgent');
        $match = Utils::ldMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, self::MOZILLA_TOLLERACE);
        return $this->userAgentsWithDeviceID[$match];
    
    }
    
    private function applyMozilla5ConclusiveMatch($userAgent)
    {
        $this->logger->log('Applying Catch All Conclusive Match Mozilla 5(LD with treshold of)for ua: $userAgent');
        $this->mozilla5UserAgentsWithDeviceID = $this->persistenceProvider->load(self::MOZILLA5);

        if (is_array($this->mozilla5UserAgentsWithDeviceID) && !array_key_exists($userAgent, $this->mozilla5UserAgentsWithDeviceID)) {
            $match = Utils::ldMatch(array_keys($this->mozilla5UserAgentsWithDeviceID), $userAgent, self::MOZILLA_TOLLERACE);
        }
        
        if (!empty($match)) {
            return $this->mozilla5UserAgentsWithDeviceID[$match];
        }
        
        return NULL;
    }
    
    private function applyMozilla4ConclusiveMatch($userAgent)
    {
        $this->logger->log('Applying Catch All Conclusive Match Mozilla 4 for ua: $userAgent');
        $this->mozilla4UserAgentsWithDeviceID = $this->persistenceProvider->load(self::MOZILLA4);
        
        if (is_array($this->mozilla4UserAgentsWithDeviceID) 
            && !array_key_exists($userAgent, $this->mozilla4UserAgentsWithDeviceID)
        ) {
            $match = Utils::ldMatch(
                array_keys($this->mozilla4UserAgentsWithDeviceID), 
                $userAgent, 
                self::MOZILLA_TOLLERACE
            );
        }
        
        if (!empty($match)) {
            return $this->mozilla4UserAgentsWithDeviceID[$match];
        }
        
        return NULL;
    }
    
    public function filter($userAgent, $deviceID)
    {
        if ($this->isMozilla4($userAgent)) {
            $this->mozilla4UserAgentsWithDeviceID[$this->normalizeUserAgent($userAgent)] = $deviceID;
        }
        if ($this->isMozilla5($userAgent)) {
            $this->mozilla5UserAgentsWithDeviceID[$this->normalizeUserAgent($userAgent)] = $deviceID;
        }
        parent::filter($userAgent, $deviceID);
    }
    
    public function persistData()
    {
        ksort($this->mozilla4UserAgentsWithDeviceID);
        ksort($this->mozilla5UserAgentsWithDeviceID);
        $this->persistenceProvider->save(self::MOZILLA4, $this->mozilla4UserAgentsWithDeviceID);
        $this->persistenceProvider->save(self::MOZILLA5, $this->mozilla5UserAgentsWithDeviceID);
        parent::persistData();
    }
    
    private function loadMozillaData()
    {
        $this->mozilla4UserAgentsWithDeviceID = $this->persistenceProvider->find(CatchAllHandler::MOZILLA4);
        $this->mozilla5UserAgentsWithDeviceID = $this->persistenceProvider->find(CatchAllHandler::MOZILLA5);
    }
    
    private function isMozilla5($userAgent)
    {
        return Utils::checkIfStartsWith($userAgent, 'Mozilla/5');
    }
    
    private function isMozilla4($userAgent)
    {
        return Utils::checkIfStartsWith($userAgent, 'Mozilla/4');
    }
    
    private function isMozilla($userAgent)
    {
        return Utils::checkIfStartsWith($userAgent, 'Mozilla');
    }
}