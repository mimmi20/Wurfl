<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Chain;

use Psr\Log\LoggerInterface;
use Wurfl\Handlers\AlcatelHandler;
use Wurfl\Handlers\AndroidHandler;
use Wurfl\Handlers\AppleHandler;
use Wurfl\Handlers\BenQHandler;
use Wurfl\Handlers\BlackBerryHandler;
use Wurfl\Handlers\BotCrawlerTranscoderHandler;
use Wurfl\Handlers\CatchAllMozillaHandler;
use Wurfl\Handlers\CatchAllRisHandler;
use Wurfl\Handlers\ChromeHandler;
use Wurfl\Handlers\DesktopApplicationHandler;
use Wurfl\Handlers\DoCoMoHandler;
use Wurfl\Handlers\FennecOnAndroidHandler;
use Wurfl\Handlers\FirefoxHandler;
use Wurfl\Handlers\FirefoxOSHandler;
use Wurfl\Handlers\GrundigHandler;
use Wurfl\Handlers\HTCHandler;
use Wurfl\Handlers\HTCMacHandler;
use Wurfl\Handlers\JavaMidletHandler;
use Wurfl\Handlers\KDDIHandler;
use Wurfl\Handlers\KindleHandler;
use Wurfl\Handlers\KonquerorHandler;
use Wurfl\Handlers\KyoceraHandler;
use Wurfl\Handlers\LGHandler;
use Wurfl\Handlers\LGUPLUSHandler;
use Wurfl\Handlers\MaemoHandler;
use Wurfl\Handlers\MitsubishiHandler;
use Wurfl\Handlers\MotorolaHandler;
use Wurfl\Handlers\MSIEHandler;
use Wurfl\Handlers\NecHandler;
use Wurfl\Handlers\NetFrontOnAndroidHandler;
use Wurfl\Handlers\NintendoHandler;
use Wurfl\Handlers\NokiaHandler;
use Wurfl\Handlers\NokiaOviBrowserHandler;
use UaNormalizer\Generic\Android as GenericAndroid;
use UaNormalizer\Generic\BlackBerry;
use UaNormalizer\Generic\CFNetwork;
use UaNormalizer\Generic\LocaleRemover;
use UaNormalizer\Generic\SerialNumbers;
use UaNormalizer\Generic\TransferEncoding;
use UaNormalizer\Generic\UCWEB;
use UaNormalizer\Generic\UPLink;
use UaNormalizer\Specific\Android;
use UaNormalizer\Specific\Apple;
use UaNormalizer\Specific\Chrome;
use UaNormalizer\Specific\DesktopApplication;
use UaNormalizer\Specific\Firefox;
use UaNormalizer\Specific\HTCMac;
use UaNormalizer\Specific\LG;
use UaNormalizer\Specific\Maemo;
use UaNormalizer\Specific\MSIE;
use UaNormalizer\Specific\Opera;
use UaNormalizer\Specific\OperaMobiOrTabletOnAndroid;
use UaNormalizer\Specific\Safari;
use UaNormalizer\Specific\UcwebU2;
use UaNormalizer\Specific\UcwebU3;
use UaNormalizer\Specific\WebOS;
use UaNormalizer\Specific\WindowsPhone;
use UaNormalizer\UserAgentNormalizer;
use Wurfl\Handlers\OperaHandler;
use Wurfl\Handlers\OperaMiniHandler;
use Wurfl\Handlers\OperaMiniOnAndroidHandler;
use Wurfl\Handlers\OperaMobiOrTabletOnAndroidHandler;
use Wurfl\Handlers\PanasonicHandler;
use Wurfl\Handlers\PantechHandler;
use Wurfl\Handlers\PhilipsHandler;
use Wurfl\Handlers\PortalmmmHandler;
use Wurfl\Handlers\QtekHandler;
use Wurfl\Handlers\ReksioHandler;
use Wurfl\Handlers\SafariHandler;
use Wurfl\Handlers\SagemHandler;
use Wurfl\Handlers\SamsungHandler;
use Wurfl\Handlers\SanyoHandler;
use Wurfl\Handlers\SharpHandler;
use Wurfl\Handlers\SiemensHandler;
use Wurfl\Handlers\SkyfireHandler;
use Wurfl\Handlers\SmartTVHandler;
use Wurfl\Handlers\SonyEricssonHandler;
use Wurfl\Handlers\SPVHandler;
use Wurfl\Handlers\TizenHandler;
use Wurfl\Handlers\ToshibaHandler;
use Wurfl\Handlers\UbuntuTouchOSHandler;
use Wurfl\Handlers\Ucweb7OnAndroidHandler;
use Wurfl\Handlers\UcwebU2Handler;
use Wurfl\Handlers\UcwebU3Handler;
use Wurfl\Handlers\VodafoneHandler;
use Wurfl\Handlers\WebOSHandler;
use Wurfl\Handlers\WindowsPhoneHandler;
use Wurfl\Handlers\WindowsRTHandler;
use Wurfl\Handlers\XboxHandler;
use Wurfl\Storage\Storage;

/**
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating
 * User Agent Handler Chains
 *
 * @see        \Wurfl\Handlers\Chain\UserAgentHandlerChain
 */
class UserAgentHandlerChainFactory
{
    /**
     * Create a \Wurfl\Handlers\Chain\UserAgentHandlerChain
     *
     * @param \Wurfl\Storage\Storage   $persistenceProvider
     * @param \Wurfl\Storage\Storage   $cacheProvider
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return UserAgentHandlerChain
     */
    public static function createFrom(
        Storage $persistenceProvider,
        Storage $cacheProvider,
        LoggerInterface $logger = null
    ) {
        /** @var $userAgentHandlerChain \Wurfl\Handlers\Chain\UserAgentHandlerChain */
        $userAgentHandlerChain = $cacheProvider->load('UserAgentHandlerChain');

        if (!($userAgentHandlerChain instanceof UserAgentHandlerChain)) {
            $userAgentHandlerChain = self::init();
            $cacheProvider->save('UserAgentHandlerChain', $userAgentHandlerChain, 3600);
        }

        $userAgentHandlerChain->setLogger($logger);

        foreach ($userAgentHandlerChain->getHandlers() as $handler) {
            /* @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler
                ->setLogger($logger)
                ->setPersistenceProvider($persistenceProvider);
        }

        return $userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects
     *
     * @return \Wurfl\Handlers\Chain\UserAgentHandlerChain
     */
    private static function init()
    {
        $userAgentHandlerChain = new UserAgentHandlerChain();

        /** @var $genericNormalizers UserAgentNormalizer */
        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        $userAgentHandlerChain->addUserAgentHandler(new SmartTVHandler($genericNormalizers));

        /**** Mobile devices ****/
        $userAgentHandlerChain->addUserAgentHandler(new KindleHandler($genericNormalizers));

        /**** UCWEB ****/
        $ucwebu3Normalizer = clone $genericNormalizers;
        $ucwebu3Normalizer->add(new UcwebU3());
        $userAgentHandlerChain->addUserAgentHandler(new UcwebU3Handler($ucwebu3Normalizer));

        $ucwebu2Normalizer = clone $genericNormalizers;
        $ucwebu2Normalizer->add(new UcwebU2());
        $userAgentHandlerChain->addUserAgentHandler(new UcwebU2Handler($ucwebu2Normalizer));

        /**** Mobile platforms ****/
        //Windows Phone must be above Android to resolve WP 8.1 and above UAs correctly
        $winPhoneNormalizer = clone $genericNormalizers;
        $winPhoneNormalizer->add(
            new WindowsPhone()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new WindowsPhoneHandler($winPhoneNormalizer)
        );

        // Android Matcher Chain
        $userAgentHandlerChain->addUserAgentHandler(
            new OperaMiniOnAndroidHandler($genericNormalizers)
        );
        $operaMobiNormalizer = clone $genericNormalizers;
        $operaMobiNormalizer->add(
            new OperaMobiOrTabletOnAndroid()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new OperaMobiOrTabletOnAndroidHandler($operaMobiNormalizer)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new FennecOnAndroidHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new Ucweb7OnAndroidHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new NetFrontOnAndroidHandler($genericNormalizers)
        );

        $androidNormalizer = clone $genericNormalizers;
        $androidNormalizer->add(new Android());
        $userAgentHandlerChain->addUserAgentHandler(new AndroidHandler($androidNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(new UbuntuTouchOSHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new TizenHandler($genericNormalizers));

        $appleNormalizer = clone $genericNormalizers;
        $appleNormalizer->add(new Apple());
        $userAgentHandlerChain->addUserAgentHandler(new AppleHandler($appleNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(
            new NokiaOviBrowserHandler($genericNormalizers)
        );

        /**** High workload mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new NokiaHandler($genericNormalizers));

        $userAgentHandlerChain->addUserAgentHandler(new SamsungHandler($genericNormalizers));

        $userAgentHandlerChain->addUserAgentHandler(
            new BlackBerryHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new SonyEricssonHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(new MotorolaHandler($genericNormalizers));

        /**** Other mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new AlcatelHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new BenQHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new DoCoMoHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new GrundigHandler($genericNormalizers));

        $htcMacNormalizer = clone $genericNormalizers;
        $htcMacNormalizer->add(new HTCMac());
        $userAgentHandlerChain->addUserAgentHandler(new HTCMacHandler($htcMacNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(new HTCHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new KDDIHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new KyoceraHandler($genericNormalizers));

        $lgNormalizer = clone $genericNormalizers;
        $lgNormalizer->add(new LG());
        $userAgentHandlerChain->addUserAgentHandler(new LGHandler($lgNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(new LGUPLUSHandler($genericNormalizers));

        $maemoNormalizer = clone $genericNormalizers;
        $maemoNormalizer->add(new Maemo());
        $userAgentHandlerChain->addUserAgentHandler(new MaemoHandler($maemoNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(
            new MitsubishiHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(new NecHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new NintendoHandler($genericNormalizers));

        $userAgentHandlerChain->addUserAgentHandler(
            new PanasonicHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(new PantechHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new PhilipsHandler($genericNormalizers));

        $userAgentHandlerChain->addUserAgentHandler(
            new PortalmmmHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(new QtekHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new ReksioHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SagemHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SanyoHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SharpHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SiemensHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SkyfireHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new SPVHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new ToshibaHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new VodafoneHandler($genericNormalizers));

        $webOSNormalizer = clone $genericNormalizers;
        $webOSNormalizer->add(new WebOS());
        $userAgentHandlerChain->addUserAgentHandler(new WebOSHandler($webOSNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(
            new FirefoxOSHandler($genericNormalizers)
        );

        $userAgentHandlerChain->addUserAgentHandler(
            new OperaMiniHandler($genericNormalizers)
        );

        /**** Java Midlets ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new JavaMidletHandler($genericNormalizers)
        );

        /**** Tablet Browsers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new WindowsRTHandler($genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new BotCrawlerTranscoderHandler($genericNormalizers)
        );

        /**** Game Consoles ****/
        $userAgentHandlerChain->addUserAgentHandler(new XboxHandler($genericNormalizers));

        /**** DesktopApplications ****/
        $desktopApplicationNormalizer = clone $genericNormalizers;
        $desktopApplicationNormalizer->add(new DesktopApplication());
        $userAgentHandlerChain->addUserAgentHandler(
            new DesktopApplicationHandler($desktopApplicationNormalizer)
        );

        /**** Desktop Browsers ****/
        //MSIE above Chrome/Opera after MSIE 12+ say Chrome
        $msieNormalizer = clone $genericNormalizers;
        $msieNormalizer->add(new MSIE());
        $userAgentHandlerChain->addUserAgentHandler(new MSIEHandler($msieNormalizer));

        $operaNormalizer = clone $genericNormalizers;
        $operaNormalizer->add(new Opera());
        $userAgentHandlerChain->addUserAgentHandler(new OperaHandler($operaNormalizer));

        $chromeNormalizer = clone $genericNormalizers;
        $chromeNormalizer->add(new Chrome());
        $userAgentHandlerChain->addUserAgentHandler(new ChromeHandler($chromeNormalizer));

        $firefoxNormalizer = clone $genericNormalizers;
        $firefoxNormalizer->add(new Firefox());
        $userAgentHandlerChain->addUserAgentHandler(new FirefoxHandler($firefoxNormalizer));

        $safariNormalizer = clone $genericNormalizers;
        $safariNormalizer->add(new Safari());
        $userAgentHandlerChain->addUserAgentHandler(new SafariHandler($safariNormalizer));

        $userAgentHandlerChain->addUserAgentHandler(
            new KonquerorHandler($genericNormalizers)
        );

        /**** All other requests ****/
        $userAgentHandlerChain->addUserAgentHandler(new CatchAllMozillaHandler($genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new CatchAllRisHandler($genericNormalizers));

        return $userAgentHandlerChain;
    }

    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     *
     * @return \UaNormalizer\UserAgentNormalizer
     */
    public static function createGenericNormalizers()
    {
        return new UserAgentNormalizer(
            array(
                new UCWEB(),
                new UPLink(),
                new SerialNumbers(),
                new LocaleRemover(),
                new CFNetwork(),
                new BlackBerry(),
                new GenericAndroid(),
                new TransferEncoding(),
            )
        );
    }
}
