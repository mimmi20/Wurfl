<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Reloader;

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
 *
 * @category   WURFL
 * @package    WURFL_Reloader
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 * @deprecated
 */
/**
 * WURFL Reloader Interface
 * @package    WURFL_Reloader
 * @deprecated
 */
interface ReloaderInterface
{
    public function reload($wurflConfigurationPath);
}