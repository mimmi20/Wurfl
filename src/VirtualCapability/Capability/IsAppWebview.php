<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Capability;

use UaNormalizer\Helper\Utils;

/**
 * Virtual capability helper
 */
class IsAppWebview extends IsApp
{
    /**
     * Simple strings or regex patterns that indicate that the UA is from a built in browser that sends webview style UAs
     * @var array
     */
    protected $blacklist = array(
        'com.android.browser',
        'com.htc.sense.browser',
        'com.asus.browser',
        'com.google.android.browser',
        'com.lenovo.browser',
        'com.huawei.android.browser',
    );

    /**
     * @return bool
     */
    protected function compute()
    {
        // We don't consider browsers apps
        if ($this->isThirdPartyBrowser()) {
            return false;
        }

        if ($this->isAndroidLollipopWebView()) {
            return true;
        }

        // Handling Chrome separately
        if ($this->isAndroidChrome()) {
            return false;
        }

        if ($this->isiOSWebView()) {
            return true;
        }

        if ($this->isMacOSXWebView()) {
            return true;
        }

        return $this->isAndroidWebView();
    }

    /**
     * @return bool
     */
    protected function isAndroidChrome()
    {
        $s = \Stringy\create($this->request->getUserAgentNormalized());

        return $this->device->getCapability('device_os') == "Android"
            && $s->contains('Chrome')
            && !$s->contains('Version');
    }
}
