<?php
namespace Wurfl\Xml;

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
     * @package    WURFL_Xml
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
/**
 * WURFL XML Parsing interface
 *
 * @package    WURFL_Xml
 */
interface XmlInterface
{
    const ID                 = "id";
    const USER_AGENT         = "user_agent";
    const FALL_BACK          = "fall_back";
    const ACTUAL_DEVICE_ROOT = "actual_device_root";
    const SPECIFIC           = "specific";

    const DEVICE = "device";

    const GROUP    = "group";
    const GROUP_ID = "id";

    const CAPABILITY       = "capability";
    const CAPABILITY_NAME  = "name";
    const CAPABILITY_VALUE = "value";
}
