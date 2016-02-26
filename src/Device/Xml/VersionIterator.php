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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Device\Xml;

/**
 * Extracts version information from XML file
 */
class VersionIterator extends AbstractIterator
{
    /**
     * @var bool
     */
    private $foundVersionInfo = false;

    /**
     *
     */
    public function readNextElement()
    {
        $version     = '';
        $lastUpdated = '';
        $officialURL = '';

        while ($this->xmlReader->read()) {
            $nodeName = $this->xmlReader->name;

            switch ($this->xmlReader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($nodeName) {
                        case 'ver':
                            $version = $this->getTextValue();
                            break;
                        case 'last_updated':
                            $lastUpdated = $this->getTextValue();
                            break;
                        case 'official_url':
                            $officialURL = $this->getTextValue();
                            break;
                        default:
                            // nothing to do here
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    switch ($nodeName) {
                        case 'version':
                            $this->foundVersionInfo = true;
                            $this->currentElement   = new Info($version, $lastUpdated, $officialURL);

                            return;
                        default:
                            // nothing to do here
                            break;
                    }
                    break;
                default:
                    // nothing to do here
                    break;
            }
        } // end of while
    }

    /**
     * @return bool
     */
    public function valid()
    {
        // We're finished with the version node, nothing else to do
        if ($this->foundVersionInfo === true) {
            return false;
        }

        if ($this->currentElement === null) {
            $this->readNextElement();
        }

        return $this->currentElement !== null;
    }
}
