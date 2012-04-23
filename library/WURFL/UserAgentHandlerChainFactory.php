<?php
declare(ENCODING = 'utf-8');
namespace WURFL;

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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating User Agent Handler Chains
 * @package    WURFL
 * @see WURFL_UserAgentHandlerChain
 */
class UserAgentHandlerChainFactory {

    /**
     * @var WURFL_UserAgentHandlerChain
     */
    private static $_userAgentHandlerChain = null;

    /**
     * Create a WURFL_UserAgentHandlerChain from the given $context
     * @param WURFL_Context $context
     * @return WURFL_UserAgentHandlerChain
     */
    public static function createFrom(Context $context) {
        $cached_data = $context->cacheProvider->load('UserAgentHandlerChain');
        if ($cached_data !== null) {
            self::$_userAgentHandlerChain = @unserialize($cached_data);
        }
        if (!(self::$_userAgentHandlerChain instanceof UserAgentHandlerChain)) {
            self::init($context);
            $context->cacheProvider->save('UserAgentHandlerChain', serialize(self::$_userAgentHandlerChain), 3600);
        }
        return self::$_userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \WURFL\Handlers\Handler objects from the given $context
     * @param WURFL_Context $context
     */
    static private function init(Context $context) {

        self::$_userAgentHandlerChain = new UserAgentHandlerChain();

        $genericNormalizers = self::createGenericNormalizers();
        
        /**** Java Midlets ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\JavaMidletHandler($context, $genericNormalizers));
        
        /**** Smart TVs ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SmartTVHandler($context, $genericNormalizers));
        
        /**** Mobile devices ****/
        $kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Kindle());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KindleHandler($context, $kindleNormalizer));
        $lguplusNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\LGUPLUS());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\LGUPLUSHandler($context, $lguplusNormalizer));
        
        /**** Mobile platforms ****/
        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Android());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AndroidHandler($context, $androidNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AppleHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\WindowsPhoneHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NokiaOviBrowserHandler($context, $genericNormalizers));
        
        /**** High workload mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NokiaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SamsungHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BlackBerryHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SonyEricssonHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MotorolaHandler($context, $genericNormalizers));
        
        /**** Other mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\AlcatelHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BenQHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\DoCoMoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\GrundigHandler($context, $genericNormalizers));
        $htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\HTCMac());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\HTCMacHandler($context, $htcMacNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\HTCHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KDDIHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KyoceraHandler($context, $genericNormalizers));
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\LG());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\LGHandler($context, $lgNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MitsubishiHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NecHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\NintendoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PanasonicHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PantechHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PhilipsHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\PortalmmmHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\QtekHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\ReksioHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SagemHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SanyoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SharpHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SiemensHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SPVHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\ToshibaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\VodafoneHandler($context, $genericNormalizers));
        $webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\WebOS());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\WebOSHandler($context, $webOSNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\OperaMiniHandler($context, $genericNormalizers));
        
        /**** Robots / Crawlers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers));
        
        /**** Desktop Browsers ****/
        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Chrome());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\ChromeHandler($context, $chromeNormalizer));
        
        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Firefox());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\FirefoxHandler($context, $firefoxNormalizer));
        
        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\MSIE());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\MSIEHandler($context, $msieNormalizer));
        
        $operaNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Opera());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\OperaHandler($context, $operaNormalizer));
        
        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Safari());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\SafariHandler($context, $safariNormalizer));
        
        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new \WURFL\Request\UserAgentNormalizer\Specific\Konqueror());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\KonquerorHandler($context, $konquerorNormalizer));
        
        
        /**** All other requests ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \WURFL\Handlers\CatchAllHandler($context, $genericNormalizers));

    }

    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     * @return WURFL_Request_UserAgentNormalizer
     */
    private static function createGenericNormalizers() {
        return new \WURFL\Request\UserAgentNormalizer(array(
            new \WURFL\Request\UserAgentNormalizer\Generic\UPLink(),
            new \WURFL\Request\UserAgentNormalizer\Generic\BlackBerry(),
            new \WURFL\Request\UserAgentNormalizer\Generic\YesWAP(),
            new \WURFL\Request\UserAgentNormalizer\Generic\BabelFish(),
            new \WURFL\Request\UserAgentNormalizer\Generic\SerialNumbers(),
            new \WURFL\Request\UserAgentNormalizer\Generic\NovarraGoogleTranslator(),
            new \WURFL\Request\UserAgentNormalizer\Generic\LocaleRemover(),
            new \WURFL\Request\UserAgentNormalizer\Generic\UCWEB(),
        ));
    }
}