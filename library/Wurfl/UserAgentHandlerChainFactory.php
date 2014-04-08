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

use WurflCache\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

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
     * Create a \Wurfl\UserAgentHandlerChain from the given $context
     *
     * @param Context                  $context
     * @param \Wurfl\Storage\Storage   $persistenceProvider
     * @param \Wurfl\Storage\Storage   $cacheProvider
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return UserAgentHandlerChain
     */
    public static function createFrom(
        Context         $context, 
        Storage\Storage $persistenceProvider, 
        Storage\Storage $cacheProvider, 
        LoggerInterface $logger = null)
    {
        /** @var $userAgentHandlerChain \Wurfl\UserAgentHandlerChain */
        $userAgentHandlerChain = $cacheProvider->load('UserAgentHandlerChain');

        if ($userAgentHandlerChain instanceof UserAgentHandlerChain) {
            foreach ($userAgentHandlerChain->getHandlers() as $handler) {
                /** @var $handler \Wurfl\Handlers\AbstractHandler */
                $handler
                    ->setLogger($logger)
                    ->setPersistenceProvider($persistenceProvider)
                ;
            }
        } else {
            $userAgentHandlerChain = self::init($context);
            $cacheProvider->save('UserAgentHandlerChain', $userAgentHandlerChain, 3600);
        }

        return $userAgentHandlerChain;
    }

    /**
     * Initializes the factory with an instance of all possible \Wurfl\Handlers\AbstractHandler objects from the given
     * $context
     *
     * @param Context $context
     *
     * @return \Wurfl\UserAgentHandlerChain
     */
    private static function init(Context $context)
    {
        $userAgentHandlerChain = new UserAgentHandlerChain();

        /** @var $genericNormalizers \Wurfl\Request\Normalizer\UserAgentNormalizer */
        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SmartTVHandler($context, $genericNormalizers));

        /**** Mobile devices ****/
        $kindleNormalizer = clone $genericNormalizers;
        $kindleNormalizer->add(new Request\Normalizer\Specific\Kindle());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KindleHandler($context, $kindleNormalizer));

        /**** UCWEB ****/
        $ucwebu2Normalizer = clone $genericNormalizers;
        $ucwebu2Normalizer->add(new Request\Normalizer\Specific\UcwebU2());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU2Handler($context, $ucwebu2Normalizer));
        $ucwebu3Normalizer = clone $genericNormalizers;
        $ucwebu3Normalizer->add(new Request\Normalizer\Specific\UcwebU3());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\UcwebU3Handler($context, $ucwebu3Normalizer));

        /**** Mobile platforms ****/
        // Android Matcher Chain
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniOnAndroidHandler($context, $genericNormalizers)
        );
        $operaMobiNormalizer = clone $genericNormalizers;
        $operaMobiNormalizer->add(
            new Request\Normalizer\Specific\OperaMobiOrTabletOnAndroid()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMobiOrTabletOnAndroidHandler($context, $operaMobiNormalizer)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FennecOnAndroidHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\Ucweb7OnAndroidHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NetFrontOnAndroidHandler($context, $genericNormalizers)
        );
        $androidNormalizer = clone $genericNormalizers;
        $androidNormalizer->add(new Request\Normalizer\Specific\Android());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AndroidHandler($context, $androidNormalizer));

        $appleNormalizer = clone $genericNormalizers;
        $appleNormalizer->add(new \Wurfl\Request\Normalizer\Specific\Apple());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AppleHandler($context, $appleNormalizer));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers)
        );
        $winPhoneNormalizer = clone $genericNormalizers;
        $winPhoneNormalizer->add(
            new Request\Normalizer\Specific\WindowsPhone()
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsPhoneHandler($context, $winPhoneNormalizer)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\NokiaOviBrowserHandler($context, $genericNormalizers)
        );

        /**** High workload mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NokiaHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SamsungHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BlackBerryHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\SonyEricssonHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MotorolaHandler($context, $genericNormalizers));

        /**** Other mobile matchers ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\AlcatelHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\BenQHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\DoCoMoHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\GrundigHandler($context, $genericNormalizers));
        
        $htcMacNormalizer = clone $genericNormalizers;
        $htcMacNormalizer->add(new Request\Normalizer\Specific\HTCMac());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCMacHandler($context, $htcMacNormalizer));
        
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\HTCHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KDDIHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\KyoceraHandler($context, $genericNormalizers));
        $lgNormalizer = clone $genericNormalizers;
        $lgNormalizer->add(new Request\Normalizer\Specific\LG());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\LGHandler($context, $lgNormalizer));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\LGUPLUSHandler($context, $genericNormalizers));
        $maemoNormalizer = clone $genericNormalizers;
        $maemoNormalizer->add(new Request\Normalizer\Specific\Maemo());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MaemoHandler($context, $maemoNormalizer));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\MitsubishiHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NecHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\NintendoHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PanasonicHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\PantechHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\PhilipsHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\PortalmmmHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\QtekHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ReksioHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SagemHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SanyoHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SharpHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SiemensHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SkyfireHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SPVHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ToshibaHandler($context, $genericNormalizers));
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\VodafoneHandler($context, $genericNormalizers));
        
        $webOSNormalizer = clone $genericNormalizers;
        $webOSNormalizer->add(new Request\Normalizer\Specific\WebOS());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\WebOSHandler($context, $webOSNormalizer));
        
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\FirefoxOSHandler($context, $genericNormalizers)
        );
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\OperaMiniHandler($context, $genericNormalizers)
        );

        /**** Java Midlets ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\JavaMidletHandler($context, $genericNormalizers)
        );

        /**** Tablet Browsers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\WindowsRTHandler($context, $genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers)
        );

        /**** Game Consoles ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\XboxHandler($context, $genericNormalizers));

        /**** DesktopApplications ****/
        $desktopApplicationNormalizer = clone $genericNormalizers;
        $desktopApplicationNormalizer->add(new \Wurfl\Request\Normalizer\Specific\DesktopApplication());
        $userAgentHandlerChain->addUserAgentHandler(new \Wurfl\Handlers\DesktopApplicationHandler($context, $desktopApplicationNormalizer));

        /**** Desktop Browsers ****/
        $chromeNormalizer = clone $genericNormalizers;
        $chromeNormalizer->add(new Request\Normalizer\Specific\Chrome());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\ChromeHandler($context, $chromeNormalizer));

        $firefoxNormalizer = clone $genericNormalizers;
        $firefoxNormalizer->add(new Request\Normalizer\Specific\Firefox());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\FirefoxHandler($context, $firefoxNormalizer));

        $msieNormalizer = clone $genericNormalizers;
        $msieNormalizer->add(new Request\Normalizer\Specific\MSIE());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\MSIEHandler($context, $msieNormalizer));

        $operaNormalizer = clone $genericNormalizers;
        $operaNormalizer->add(new Request\Normalizer\Specific\Opera());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\OperaHandler($context, $operaNormalizer));

        $safariNormalizer = clone $genericNormalizers;
        $safariNormalizer->add(new Request\Normalizer\Specific\Safari());
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\SafariHandler($context, $safariNormalizer));

        $konquerorNormalizer = clone $genericNormalizers;
        $konquerorNormalizer->add(new Request\Normalizer\Specific\Konqueror());
        $userAgentHandlerChain->addUserAgentHandler(
            new Handlers\KonquerorHandler($context, $konquerorNormalizer)
        );

        /**** All other requests ****/
        $userAgentHandlerChain->addUserAgentHandler(new Handlers\CatchAllHandler($context, $genericNormalizers));
        
        return $userAgentHandlerChain;
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
