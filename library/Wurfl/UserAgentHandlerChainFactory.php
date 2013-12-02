<?php
namespace Wurfl;

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
 * @see \Wurfl\UserAgentHandlerChain
 */
class UserAgentHandlerChainFactory {

    /**
     * @var \Wurfl\UserAgentHandlerChain
     */
    private static $_userAgentHandlerChain = null;

    /**
     * Create a \Wurfl\UserAgentHandlerChain from the given $context
     * @param \Wurfl\Context $context
     * @return \Wurfl\UserAgentHandlerChain
     */
    public static function createFrom(\Wurfl\Context $context) {
        $cached_data = $context->cacheProvider->load('UserAgentHandlerChain');
        if ($cached_data !== null) {
            self::$_userAgentHandlerChain = $cached_data;
            foreach (self::$_userAgentHandlerChain->getHandlers() as $handler) {
                $handler->setupContext($context);
            }
        }
        if (!(self::$_userAgentHandlerChain instanceof \Wurfl\UserAgentHandlerChain)) {
            self::init($context);
            $context->cacheProvider->save('UserAgentHandlerChain', self::$_userAgentHandlerChain, 3600);
        }
        return self::$_userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects from the given $context
     * @param \Wurfl\Context $context
     */
    static private function init(\Wurfl\Context $context) {

        self::$_userAgentHandlerChain = new \Wurfl\UserAgentHandlerChain();

        $genericNormalizers = self::createGenericNormalizers();
        
        /**** Smart TVs ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SmartTVHandler($context, $genericNormalizers));
        
        /**** Mobile devices ****/
        $kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Kindle());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\KindleHandler($context, $kindleNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\LGUPLUSHandler($context, $genericNormalizers));
        
        /**** UCWEB ****/
        $ucwebu2Normalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\UcwebU2());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\UcwebU2Handler($context, $ucwebu2Normalizer));
        $ucwebu3Normalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\UcwebU3());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\UcwebU3Handler($context, $ucwebu3Normalizer));
        
        /**** Java Midlets ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\JavaMidletHandler($context, $genericNormalizers));
        
        /**** Mobile platforms ****/
        // Android Matcher Chain
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\OperaMiniOnAndroidHandler($context, $genericNormalizers));
        $operaMobiNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\OperaMobiOrTabletOnAndroid());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\OperaMobiOrTabletOnAndroidHandler($context, $operaMobiNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\FennecOnAndroidHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\Ucweb7OnAndroidHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\NetFrontOnAndroidHandler($context, $genericNormalizers));
        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Android());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\AndroidHandler($context, $androidNormalizer));
        
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\AppleHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers));
        $winPhoneNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\WindowsPhone());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\WindowsPhoneHandler($context, $winPhoneNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\NokiaOviBrowserHandler($context, $genericNormalizers));
        
        /**** High workload mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\NokiaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SamsungHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\BlackBerryHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SonyEricssonHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\MotorolaHandler($context, $genericNormalizers));
        
        /**** Other mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\AlcatelHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\BenQHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\DoCoMoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\GrundigHandler($context, $genericNormalizers));
        $htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\HTCMac());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\HTCMacHandler($context, $htcMacNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\HTCHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\KDDIHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\KyoceraHandler($context, $genericNormalizers));
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\LG());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\LGHandler($context, $lgNormalizer));
        $maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Maemo());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\MaemoHandler($context, $maemoNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\MitsubishiHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\NecHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\NintendoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\PanasonicHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\PantechHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\PhilipsHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\PortalmmmHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\QtekHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\ReksioHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SagemHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SanyoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SharpHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SiemensHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SkyfireHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SPVHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\ToshibaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\VodafoneHandler($context, $genericNormalizers));
        $webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\WebOS());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\WebOSHandler($context, $webOSNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\FirefoxOSHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\OperaMiniHandler($context, $genericNormalizers));
        
        /**** Tablet Browsers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\WindowsRTHandler($context, $genericNormalizers));
        
        /**** Robots / Crawlers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers));
        
        /**** Game Consoles ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\XboxHandler($context, $genericNormalizers));
        
        /**** Desktop Browsers ****/
        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Chrome());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\ChromeHandler($context, $chromeNormalizer));
        
        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Firefox());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\FirefoxHandler($context, $firefoxNormalizer));
        
        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\MSIE());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\MSIEHandler($context, $msieNormalizer));
        
        $operaNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Opera());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\OperaHandler($context, $operaNormalizer));
        
        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Safari());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\SafariHandler($context, $safariNormalizer));
        
        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new \Wurfl\Request\Normalizer\Specific\Konqueror());
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\KonquerorHandler($context, $konquerorNormalizer));
        
        
        /**** All other requests ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\CatchAllHandler($context, $genericNormalizers));

    }
    
    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     * @return \Wurfl\Request\Normalizer\UserAgentNormalizer
     */
    private static function createGenericNormalizers() {
        return new \Wurfl\Request\Normalizer\UserAgentNormalizer(array(
            new \Wurfl\Request\Normalizer\Generic\UCWEB(),
            new \Wurfl\Request\Normalizer\Generic\UPLink(),
            new \Wurfl\Request\Normalizer\Generic\SerialNumbers(),
            new \Wurfl\Request\Normalizer\Generic\LocaleRemover(),
            new \Wurfl\Request\Normalizer\Generic\BlackBerry(),
            new \Wurfl\Request\Normalizer\Generic\YesWAP(),
            new \Wurfl\Request\Normalizer\Generic\BabelFish(),
            new \Wurfl\Request\Normalizer\Generic\NovarraGoogleTranslator(),
            new \Wurfl\Request\Normalizer\Generic\TransferEncoding(),
        ));
    }
}