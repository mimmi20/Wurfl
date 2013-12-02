<?php
namespace Wurfl\Request\Normalizer;

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
class UserAgentNormalizer implements NormalizerInterface
{

    /**
     * UserAgentNormalizer chain - array of \Wurfl\Request\Normalizer\UserAgentNormalizer objects
     * @var array
     */
    protected $_userAgentNormalizers = array();

    /**
     * Set the User Agent Normalizers
     * @param array $normalizers Array of \Wurfl\Request\Normalizer\UserAgentNormalizer objects
     */
    public function __construct($normalizers = array()) {
        if(is_array($normalizers)) {
            $this->_userAgentNormalizers = $normalizers;
        }
    }

    /**
     * Adds a new UserAgent Normalizer to the chain
     *
*@param ormalizerInterface $normalizer
     *
*@return \Wurfl\Request\Normalizer\UserAgentNormalizer
     */
    public function addUserAgentNormalizer(NormalizerInterface $normalizer) {
        $userAgentNormalizers = $this->_userAgentNormalizers;
        $userAgentNormalizers[] = $normalizer;
        return new \Wurfl\Request\Normalizer\UserAgentNormalizer($userAgentNormalizers);
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
        // Don't normalize generic user agents
        if (substr($userAgent, 0, 12) == 'DO_NOT_MATCH') {
            return $userAgent;
        }
        $normalizedUserAgent = $userAgent;
        foreach ($this->_userAgentNormalizers as $normalizer) {
            $normalizedUserAgent = $normalizer->normalize($normalizedUserAgent);
        }
        return $normalizedUserAgent;
    }
}

