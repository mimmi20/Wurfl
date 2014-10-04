<?php
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
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl;

use Psr\Log\LoggerInterface;
use WurflCache\Adapter\AdapterInterface;

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
     * Create a \Wurfl\UserAgentHandlerChain
     *
     * @param \Wurfl\Storage\Storage   $persistenceProvider
     * @param \Wurfl\Storage\Storage   $cacheProvider
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return UserAgentHandlerChain
     */
    public static function createFrom(
        Storage\Storage $persistenceProvider,
        Storage\Storage $cacheProvider,
        LoggerInterface $logger = null
    ) {
        /** @var $userAgentHandlerChain \Wurfl\UserAgentHandlerChain */
        $userAgentHandlerChain = $cacheProvider->load('UserAgentHandlerChain');

        if (!($userAgentHandlerChain instanceof UserAgentHandlerChain)) {
            $userAgentHandlerChain = self::init();
            $cacheProvider->save('UserAgentHandlerChain', $userAgentHandlerChain, 3600);
        }

        foreach ($userAgentHandlerChain->getHandlers() as $handler) {
            /** @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler
                ->setLogger($logger)
                ->setPersistenceProvider($persistenceProvider)
            ;
        }

        return $userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects
     *
     * @return \Wurfl\UserAgentHandlerChain
     */
    private static function init()
    {
        $userAgentHandlerChain = new UserAgentHandlerChain();

        /** @var $genericNormalizers \Wurfl\Request\Normalizer\UserAgentNormalizer */
        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SmartTVHandler($genericNormalizers));

        /**** Mobile devices ****/
        $kindleNormalizer = clone $genericNormalizers;
        $kindleNormalizer->add(new Request\Normalizer\Specific\Kindle());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KindleHandler($kindleNormalizer));

        /**** UCWEB ****/
        $ucwebu2Normalizer = clone $genericNormalizers;
        $ucwebu2Normalizer->add(new Request\Normalizer\Specific\UcwebU2());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU2Handler($ucwebu2Normalizer));
        $ucwebu3Normalizer = clone $genericNormalizers;
        $ucwebu3Normalizer->add(new Request\Normalizer\Specific\UcwebU3());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU3Handler($ucwebu3Normalizer));

        /**** Mobile platforms ****/
        //Windows Phone must be above Android to resolve WP 8.1 and above UAs correctly
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneDesktopHandler($genericNormalizers)
        );
        $winPhoneNormalizer = clone $genericNormalizers;
        $winPhoneNormalizer->add(
            new Request\Normalizer\Specific\WindowsPhone()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneHandler($winPhoneNormalizer)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NokiaOviBrowserHandler($genericNormalizers)
        );

        // Android Matcher Chain
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniOnAndroidHandler($genericNormalizers)
        );
        $operaMobiNormalizer = clone $genericNormalizers;
        $operaMobiNormalizer->add(
            new Request\Normalizer\Specific\OperaMobiOrTabletOnAndroid()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMobiOrTabletOnAndroidHandler($operaMobiNormalizer)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FennecOnAndroidHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\Ucweb7OnAndroidHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NetFrontOnAndroidHandler($genericNormalizers)
        );
        $androidNormalizer = clone $genericNormalizers;
        $androidNormalizer->add(new Request\Normalizer\Specific\Android());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AndroidHandler($androidNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(new Handlers\UbuntuTouchOSHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\TizenHandler($genericNormalizers));

        $appleNormalizer = clone $genericNormalizers;
        $appleNormalizer->add(new Request\Normalizer\Specific\Apple());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AppleHandler($appleNormalizer));

        /**** High workload mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NokiaHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SamsungHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BlackBerryHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\SonyEricssonHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MotorolaHandler($genericNormalizers));

        /**** Other mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AlcatelHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\BenQHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\DoCoMoHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\GrundigHandler($genericNormalizers));

        $htcMacNormalizer = clone $genericNormalizers;
        $htcMacNormalizer->add(new Request\Normalizer\Specific\HTCMac());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCMacHandler($htcMacNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KDDIHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KyoceraHandler($genericNormalizers));
        $lgNormalizer = clone $genericNormalizers;
        $lgNormalizer->add(new Request\Normalizer\Specific\LG());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\LGHandler($lgNormalizer));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\LGUPLUSHandler($genericNormalizers));
        $maemoNormalizer = clone $genericNormalizers;
        $maemoNormalizer->add(new Request\Normalizer\Specific\Maemo());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MaemoHandler($maemoNormalizer));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\MitsubishiHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NecHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NintendoHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PanasonicHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\PantechHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\PhilipsHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PortalmmmHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\QtekHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ReksioHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SagemHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SanyoHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SharpHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SiemensHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SkyfireHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SPVHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ToshibaHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\VodafoneHandler($genericNormalizers));

        $webOSNormalizer = clone $genericNormalizers;
        $webOSNormalizer->add(new Request\Normalizer\Specific\WebOS());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\WebOSHandler($webOSNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FirefoxOSHandler($genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniHandler($genericNormalizers)
        );

        /**** Java Midlets ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\JavaMidletHandler($genericNormalizers)
        );

        /**** Tablet Browsers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsRTHandler($genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BotCrawlerTranscoderHandler($genericNormalizers)
        );

        /**** Game Consoles ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\XboxHandler($genericNormalizers));

        /**** Desktop Browsers - Opera and Chrome ****/
        $operaNormalizer = clone $genericNormalizers;
        $operaNormalizer->add(new Request\Normalizer\Specific\Opera());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaHandler($operaNormalizer));

        $chromeNormalizer = clone $genericNormalizers;
        $chromeNormalizer->add(new Request\Normalizer\Specific\Chrome());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromeHandler($chromeNormalizer));

        /**** DesktopApplications ****/
        $desktopApplicationNormalizer = clone $genericNormalizers;
        $desktopApplicationNormalizer->add(new Request\Normalizer\Specific\DesktopApplication());
        $userAgentHandlerChain->addUserAgentHandler(
            new \Wurfl\Handlers\DesktopApplicationHandler($desktopApplicationNormalizer)
        );

        /**** Desktop Browsers ****/
        $firefoxNormalizer = clone $genericNormalizers;
        $firefoxNormalizer->add(new Request\Normalizer\Specific\Firefox());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\FirefoxHandler($firefoxNormalizer));

        $msieNormalizer = clone $genericNormalizers;
        $msieNormalizer->add(new Request\Normalizer\Specific\MSIE());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MSIEHandler($msieNormalizer));

        $safariNormalizer = clone $genericNormalizers;
        $safariNormalizer->add(new Request\Normalizer\Specific\Safari());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SafariHandler($safariNormalizer));

        $konquerorNormalizer = clone $genericNormalizers;
        $konquerorNormalizer->add(new Request\Normalizer\Specific\Konqueror());
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\KonquerorHandler($konquerorNormalizer)
        );

        /**** All other requests ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\CatchAllHandler($genericNormalizers));

        return $userAgentHandlerChain;
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
