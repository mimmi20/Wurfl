<?php
declare(ENCODING = 'utf-8');
namespace Wurfl;

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
class UserAgentHandlerChainFactory
{

    /**
     * @var WURFL_UserAgentHandlerChain
     */
    private static $_userAgentHandlerChain = null;

    /**
     * Create a WURFL_UserAgentHandlerChain from the given $context
     * @param WURFL_Context $context
     * @return WURFL_UserAgentHandlerChain
     */
    public static function createFrom(Context $context)
    {
        self::init($context);
        return self::$_userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible Handlers\Handler objects from the given $context
     * @param WURFL_Context $context
     */
    static private function init(Context $context)
    {
        self::$_userAgentHandlerChain = new UserAgentHandlerChain();

        $genericNormalizers = self::_createGenericNormalizers();

        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Chrome());
        $chromiumNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Chromium());
        $rockmeltNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Rockmelt());
        $ironNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Iron());
        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Konqueror());
        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Safari());
        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Firefox());
        $seamonkeyNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Seamonkey());
        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\MSIE());
        $thunderbirdNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Thunderbird());
        $flockNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Flock());

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\NokiaHandler($context, $genericNormalizers));
        $lguplusNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\LGUPLUSNormalizer());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\LGUPLUSHandler($context, $genericNormalizers));

        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Android());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AndroidHandler($context, $androidNormalizer));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SonyEricssonHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MotorolaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\BlackBerryHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SiemensHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SagemHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SamsungHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PanasonicHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\NecHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\QtekHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MitsubishiHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PhilipsHandler($context, $genericNormalizers));
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\LGNormalizer());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\LGHandler($context, $lgNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AppleHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KyoceraHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AlcatelHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SharpHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SanyoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\BenQHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PantechHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ToshibaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\GrundigHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\VodafoneHandler($context, $genericNormalizers));


        // BOT
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers));


        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SPVHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\WindowsCEHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\PortalmmmHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\DoCoMoHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KDDIHandler($context, $genericNormalizers));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaMiniHandler($context, $genericNormalizers));
        $maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\Maemo());
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MaemoBrowserHandler($context, $maemoNormalizer));


        // Web Browsers handlers
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\IronHandler($context, $ironNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromiumHandler($context, $chromiumNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\RockmeltHandler($context, $rockmeltNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\FlockHandler($context, $flockNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromeHandler($context, $chromeNormalizer));
        //self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\AOLHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaHandler($context, $genericNormalizers));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\KonquerorHandler($context, $konquerorNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SafariHandler($context, $safariNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\ThunderbirdHandler($context, $thunderbirdNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\SeamonkeyHandler($context, $seamonkeyNormalizer));
        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\FirefoxHandler($context, $firefoxNormalizer));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\MSIEHandler($context, $msieNormalizer));

        self::$_userAgentHandlerChain->addUserAgentHandler(new Handlers\CatchAllHandler($context, $genericNormalizers));

    }

    /**
     * Returns an array of all possible User Agent Normalizers
     * @return array Array of Request\UserAgentNormalizer objects
     */
    private static function _createGenericNormalizers()
    {
        return new Request\UserAgentNormalizer(
            array(
                new Request\UserAgentNormalizer\Generic\UPLink(),
                new Request\UserAgentNormalizer\Generic\BlackBerry(),
                new Request\UserAgentNormalizer\Generic\YesWAP(),
                new Request\UserAgentNormalizer\Generic\BabelFish(),
                new Request\UserAgentNormalizer\Generic\SerialNumbers(),
                new Request\UserAgentNormalizer\Generic\NovarraGoogleTranslator(),
                new Request\UserAgentNormalizer\Generic\LocaleRemover()
            )
        );
    }
}