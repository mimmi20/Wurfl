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
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating
 * User Agent Handler Chains
 *
 * @package    WURFL
 * @see        \Wurfl\UserAgentHandlerChain
 */
class UserAgentHandlerChainFactory
{

    /**
     * @var UserAgentHandlerChain
     */
    private static $userAgentHandlerChain = null;

    /**
     * Create a \Wurfl\UserAgentHandlerChain from the given $context
     *
     * @param Context $context
     *
     * @return UserAgentHandlerChain
     */
    public static function createFrom(Context $context)
    {
        $cachedData = $context->cacheProvider->load('UserAgentHandlerChain');

        if ($cachedData !== null) {
            self::$userAgentHandlerChain = $cachedData;

            foreach (self::$userAgentHandlerChain->getHandlers() as $handler) {
                /** @var $handler \Wurfl\Handlers\AbstractHandler */
                $handler->setupContext($context);
            }
        }

        if (!(self::$userAgentHandlerChain instanceof UserAgentHandlerChain)) {
            self::init($context);
            $context->cacheProvider->save('UserAgentHandlerChain', self::$userAgentHandlerChain, 3600);
        }

        return self::$userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects from the given
     * $context
     *
     * @param Context $context
     */
    private static function init(Context $context)
    {

        self::$userAgentHandlerChain = new UserAgentHandlerChain();

        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SmartTVHandler($context, $genericNormalizers));

        /**** Mobile devices ****/
        $kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Kindle());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\KindleHandler($context, $kindleNormalizer));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\LGUPLUSHandler($context, $genericNormalizers));

        /**** UCWEB ****/
        $ucwebu2Normalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\UcwebU2());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU2Handler($context, $ucwebu2Normalizer));
        $ucwebu3Normalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\UcwebU3());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU3Handler($context, $ucwebu3Normalizer));

        /**** Java Midlets ****/
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\JavaMidletHandler($context, $genericNormalizers)
        );

        /**** Mobile platforms ****/
        // Android Matcher Chain
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniOnAndroidHandler($context, $genericNormalizers)
        );
        $operaMobiNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\Normalizer\Specific\OperaMobiOrTabletOnAndroid()
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMobiOrTabletOnAndroidHandler($context, $operaMobiNormalizer)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FennecOnAndroidHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\Ucweb7OnAndroidHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NetFrontOnAndroidHandler($context, $genericNormalizers)
        );
        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Android());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\AndroidHandler($context, $androidNormalizer));

        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\AppleHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers)
        );
        $winPhoneNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\Normalizer\Specific\WindowsPhone()
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneHandler($context, $winPhoneNormalizer)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NokiaOviBrowserHandler($context, $genericNormalizers)
        );

        /**** High workload mobile matchers ****/
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\NokiaHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SamsungHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BlackBerryHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\SonyEricssonHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\MotorolaHandler($context, $genericNormalizers));

        /**** Other mobile matchers ****/
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\AlcatelHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\BenQHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\DoCoMoHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\GrundigHandler($context, $genericNormalizers));
        $htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\HTCMac());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCMacHandler($context, $htcMacNormalizer));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\KDDIHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\KyoceraHandler($context, $genericNormalizers));
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\LG());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\LGHandler($context, $lgNormalizer));
        $maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Maemo());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\MaemoHandler($context, $maemoNormalizer));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\MitsubishiHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\NecHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\NintendoHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PanasonicHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\PantechHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\PhilipsHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PortalmmmHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\QtekHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\ReksioHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SagemHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SanyoHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SharpHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SiemensHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SkyfireHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SPVHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\ToshibaHandler($context, $genericNormalizers));
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\VodafoneHandler($context, $genericNormalizers));
        $webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\WebOS());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\WebOSHandler($context, $webOSNormalizer));
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FirefoxOSHandler($context, $genericNormalizers)
        );
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniHandler($context, $genericNormalizers)
        );

        /**** Tablet Browsers ****/
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsRTHandler($context, $genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers)
        );

        /**** Game Consoles ****/
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\XboxHandler($context, $genericNormalizers));

        /**** Desktop Browsers ****/
        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Chrome());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromeHandler($context, $chromeNormalizer));

        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Firefox());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\FirefoxHandler($context, $firefoxNormalizer));

        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\MSIE());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\MSIEHandler($context, $msieNormalizer));

        $operaNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Opera());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaHandler($context, $operaNormalizer));

        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Safari());
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\SafariHandler($context, $safariNormalizer));

        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\Normalizer\Specific\Konqueror());
        self::$userAgentHandlerChain->addUserAgentHandler(
            new Handlers\KonquerorHandler($context, $konquerorNormalizer)
        );

        /**** All other requests ****/
        self::$userAgentHandlerChain->addUserAgentHandler(new Handlers\CatchAllHandler($context, $genericNormalizers));
    }

    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     *
     * @return Request\Normalizer\UserAgentNormalizer
     */
    private static function createGenericNormalizers()
    {
        return new Request\Normalizer\UserAgentNormalizer(
            array(
                 new Request\Normalizer\Generic\UCWEB(),
                 new Request\Normalizer\Generic\UPLink(),
                 new Request\Normalizer\Generic\SerialNumbers(),
                 new Request\Normalizer\Generic\LocaleRemover(),
                 new Request\Normalizer\Generic\BlackBerry(),
                 new Request\Normalizer\Generic\YesWAP(),
                 new Request\Normalizer\Generic\BabelFish(),
                 new Request\Normalizer\Generic\NovarraGoogleTranslator(),
                 new Request\Normalizer\Generic\TransferEncoding(),
            )
        );
    }
}
