<?php
declare(ENCODING = 'utf-8');
namespace WURFL\Request;

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
 * @category   WURFL
 * @package    WURFL_Request
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * User Agent Normalizer
 * @package    WURFL_Request
 */
class UserAgentNormalizer implements UserAgentNormalizer\NormalizerInterface {

    /**
     * UserAgentNormalizer chain - array of WURFL_Request_UserAgentNormalizer objects
     * @var array
     */
    protected $_userAgentNormalizers = array();
    
    /**
     * Set the User Agent Normalizers
     * @param array $normalizers Array of WURFL_Request_UserAgentNormalizer objects
     */
    public function __construct($normalizers = array()) {
        if(is_array($normalizers)) {
            $this->_userAgentNormalizers = $normalizers;
        }
    }
    
    /**
     * Adds a new UserAgent Normalizer to the chain
     * @param \WURFL\Request\UserAgentNormalizer\NormalizerInterface $normalizer
     * @return WURFL_Request_UserAgentNormalizer
     */
    public function addUserAgentNormalizer(UserAgentNormalizer\NormalizerInterface $normalizer) {
        $userAgentNormalizers = $this->_userAgentNormalizers; 
        $userAgentNormalizers[] = $normalizer;
        return new UserAgentNormalizer($userAgentNormalizers);
    }
    
    /**
     * Return the number of normalizers currently registered
     * @return int count
     */
    public function count() {
        return count($this->_userAgentNormalizers);
    }
    
    /**
     * Normalize the given $userAgent by passing down the chain 
     * of normalizers
     *
     * @param string $userAgent
     * @return string Normalized user agent
     */
    public function normalize($userAgent) {
        $normalizedUserAgent = $userAgent;
        foreach ($this->_userAgentNormalizers as $normalizer) {
            $normalizedUserAgent = $normalizer->normalize($normalizedUserAgent);
//if ($userAgent == 'Mozilla/5.0 (SymbianOS/9.1; U; en-us) AppleWebKit/413 (KHTML, like Gecko) Safari/413') echo "Normalized via ".get_class($normalizer)." to ".$normalizedUserAgent."\n";
        }
        return $normalizedUserAgent;
    }
}

