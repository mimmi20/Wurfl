<?php

$wurfl['main-file'] = 'wurfl-regression.xml';
$wurfl['patches']   = array('web_browsers_patch.xml', 'spv_patch.xml');

$persistence['provider'] = 'memcache';
$persistence['params']   = array(
    'dir' => 'cache/',
);

$cache['provider'] = 'null';

$configuration['wurfl']        = $wurfl;
$configuration['allow-reload'] = true;
$configuration['persistence']  = $persistence;
$configuration['cache']        = $cache;
