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
 *
 * @package    WURFL
 * @see        \Wurfl\UserAgentHandlerChain
 */
class UserAgentHandlerChainFactory
{

    /**
     * @var UserAgentHandlerChain
     */
    private static $_userAgentHandlerChain = null;

    /**
     * Create a \Wurfl\UserAgentHandlerChain from the given $context
     *
     * @param Context $context
     *
     * @return UserAgentHandlerChain
     */
    public static function createFrom(Context $context)
    {
        $cached_data = $context->cacheProvider->load('UserAgentHandlerChain');
        if ($cached_data !== null) {
            self::$_userAgentHandlerChain = $cached_data;
            foreach (self::$_userAgentHandlerChain->getHandlers() as $handler) {
                $handler->setupContext($context);
            }
        }
        if (!(self::$_userAgentHandlerChain instanceof UserAgentHandlerChain)) {
            self::init($context);
            $context->cacheProvider->save('UserAgentHandlerChain', self::$_userAgentHandlerChain, 3600);
        }
        return self::$_userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects from the given $context
     *
     * @param Context $context
     */
    static private function init(Context $context)
    {

        self::$_userAgentHandlerChain = new UserAgentHandlerChain();

        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SmartTVHandler($context, $genericNormalizers));

        /**** Mobile devices ****/
        $kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Kindle());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KindleHandler($context, $kindleNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\LGUPLUSHandler($context, $genericNormalizers));

        /**** UCWEB ****/
        $ucwebu2Normalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\UcwebU2());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU2Handler($context, $ucwebu2Normalizer));
        $ucwebu3Normalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\UcwebU3());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU3Handler($context, $ucwebu3Normalizer));

        /**** Java Midlets ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\JavaMidletHandler($context, $genericNormalizers)
        );

        /**** Mobile platforms ****/
        // Android Matcher Chain
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniOnAndroidHandler($context, $genericNormalizers)
        );
        $operaMobiNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\Normalizer\Specific\OperaMobiOrTabletOnAndroid()
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMobiOrTabletOnAndroidHandler($context, $operaMobiNormalizer)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FennecOnAndroidHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\Ucweb7OnAndroidHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NetFrontOnAndroidHandler($context, $genericNormalizers)
        );
        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Android());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AndroidHandler($context, $androidNormalizer));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AppleHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers)
        );
        $winPhoneNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\Normalizer\Specific\WindowsPhone()
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneHandler($context, $winPhoneNormalizer)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NokiaOviBrowserHandler($context, $genericNormalizers)
        );

        /**** High workload mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\NokiaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SamsungHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BlackBerryHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\SonyEricssonHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MotorolaHandler($context, $genericNormalizers));

        /**** Other mobile matchers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AlcatelHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\BenQHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\DoCoMoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\GrundigHandler($context, $genericNormalizers));
        $htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\HTCMac());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCMacHandler($context, $htcMacNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KDDIHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KyoceraHandler($context, $genericNormalizers));
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\LG());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\LGHandler($context, $lgNormalizer));
        $maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Maemo());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MaemoHandler($context, $maemoNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\MitsubishiHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\NecHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\NintendoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PanasonicHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PantechHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PhilipsHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PortalmmmHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\QtekHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ReksioHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SagemHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SanyoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SharpHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SiemensHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SkyfireHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SPVHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ToshibaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\VodafoneHandler($context, $genericNormalizers));
        $webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\WebOS());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\WebOSHandler($context, $webOSNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FirefoxOSHandler($context, $genericNormalizers)
        );
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniHandler($context, $genericNormalizers)
        );

        /**** Tablet Browsers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsRTHandler($context, $genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers)
        );

        /**** Game Consoles ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\XboxHandler($context, $genericNormalizers));

        /**** Desktop Browsers ****/
        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Chrome());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromeHandler($context, $chromeNormalizer));

        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Firefox());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\FirefoxHandler($context, $firefoxNormalizer));

        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\MSIE());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MSIEHandler($context, $msieNormalizer));

        $operaNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Opera());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaHandler($context, $operaNormalizer));

        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Safari());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SafariHandler($context, $safariNormalizer));

        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Konqueror());
        self::$_userAgentHandlerChain->addUserAgentHandler(
            new Handlers\KonquerorHandler($context, $konquerorNormalizer)
        );

        /**** All other requests ****/
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\CatchAllHandler($context, $genericNormalizers));
    }

    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     *
     * @return Request\Normalizer\UserAgentNormalizer
     */
    private static function createGenericNormalizers()
    {
        return new Request\Normalizer\UserAgentNormalizer(array(
                                                               new Request\Normalizer\Generic\UCWEB(),
                                                               new Request\Normalizer\Generic\UPLink(),
                                                               new Request\Normalizer\Generic\SerialNumbers(),
                                                               new Request\Normalizer\Generic\LocaleRemover(),
                                                               new Request\Normalizer\Generic\BlackBerry(),
                                                               new Request\Normalizer\Generic\YesWAP(),
                                                               new Request\Normalizer\Generic\BabelFish(),
                                                               new Request\Normalizer\Generic\NovarraGoogleTranslator(),
                                                               new Request\Normalizer\Generic\TransferEncoding(),
                                                          ));
    }
}