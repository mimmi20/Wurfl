<?php
namespace Wurfl;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    WURFL
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
use SplDoublyLinkedList;

/**
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating User Agent Handler Chains
 *
 * @package    WURFL
 */
class UserAgentHandlerChainFactory
{
    /**
     * Create a UserAgentHandlerChain from the given $context
     *
     * @param Context $context
     *
     * @return SplDoublyLinkedList
     */
    public static function createFrom(Context $context)
    {
        $chain = $context->cacheProvider->load('UserAgentHandlerChain');

        if (!($chain instanceof SplDoublyLinkedList )) {
            $chain = self::init($context);
            
            $context->cacheProvider->save('UserAgentHandlerChain', $chain, 3600);
        }

        return $chain;
    }

    /**
     * Initializes the factory with an instance of all possible Handlers\Handler objects from the given $context
     *
     * @param Context $context
     *
     * @return SplDoublyLinkedList
     */
    static private function init(Context $context)
    {
        $chain = new SplDoublyLinkedList();
        $chain->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP);

        /** @var $genericNormalizers Request\UserAgentNormalizer */
        $genericNormalizers = self::createGenericNormalizers();

        /**** Smart TVs ****/
        $chain->push(new Handlers\SmartTVHandler($context, $genericNormalizers));

        /**** Mobile devices ****/
        /** @var $kindleNormalizer Request\UserAgentNormalizer */
        $kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Kindle()
        );

        $chain->push(new Handlers\KindleHandler($context, $kindleNormalizer));
        $chain->push(new Handlers\LGUPLUSHandler($context, $genericNormalizers));

        /**** UCWEB ****/
        /** @var $ucwebu2Normalizer Request\UserAgentNormalizer */
        $ucwebu2Normalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\UcwebU2()
        );
        $chain->push(new Handlers\UcwebU2Handler($context, $ucwebu2Normalizer));

        /** @var $ucwebu3Normalizer Request\UserAgentNormalizer */
        $ucwebu3Normalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\UcwebU3()
        );
        $chain->push(new Handlers\UcwebU3Handler($context, $ucwebu3Normalizer));

        /**** Java Midlets ****/
        $chain->push(
            new Handlers\JavaMidletHandler($context, $genericNormalizers)
        );

        /**** Mobile platforms ****/
        // Android Matcher Chain
        $chain->push(
            new Handlers\OperaMiniOnAndroidHandler($context, $genericNormalizers)
        );

        /** @var $operaMobiNormalizer Request\UserAgentNormalizer */
        $operaMobiNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\OperaMobiOrTabletOnAndroid()
        );
        $chain->push(
            new Handlers\OperaMobiOrTabletOnAndroidHandler($context, $operaMobiNormalizer)
        );
        $chain->push(
            new Handlers\FennecOnAndroidHandler($context, $genericNormalizers)
        );
        $chain->push(
            new Handlers\Ucweb7OnAndroidHandler($context, $genericNormalizers)
        );
        $chain->push(
            new Handlers\NetFrontOnAndroidHandler($context, $genericNormalizers)
        );

        /** @var $androidNormalizer Request\UserAgentNormalizer */
        $androidNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Android()
        );
        $chain->push(new Handlers\AndroidHandler($context, $androidNormalizer));

        $chain->push(new Handlers\AppleHandler($context, $genericNormalizers));
        $chain->push(
            new Handlers\WindowsPhoneDesktopHandler($context, $genericNormalizers)
        );

        /** @var $winPhoneNormalizer Request\UserAgentNormalizer */
        $winPhoneNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\WindowsPhone()
        );
        $chain->push(
            new Handlers\WindowsPhoneHandler($context, $winPhoneNormalizer)
        );
        $chain->push(
            new Handlers\NokiaOviBrowserHandler($context, $genericNormalizers)
        );

        /**** High workload mobile matchers ****/
        $chain->push(new Handlers\NokiaHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SamsungHandler($context, $genericNormalizers));
        $chain->push(
            new Handlers\BlackBerryHandler($context, $genericNormalizers)
        );
        $chain->push(
            new Handlers\SonyEricssonHandler($context, $genericNormalizers)
        );
        $chain->push(new Handlers\MotorolaHandler($context, $genericNormalizers));

        /**** Other mobile matchers ****/
        $chain->push(new Handlers\AlcatelHandler($context, $genericNormalizers));
        $chain->push(new Handlers\BenQHandler($context, $genericNormalizers));
        $chain->push(new Handlers\DoCoMoHandler($context, $genericNormalizers));
        $chain->push(new Handlers\GrundigHandler($context, $genericNormalizers));

        /** @var $htcMacNormalizer Request\UserAgentNormalizer */
        $htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\HTCMac()
        );
        $chain->push(new Handlers\HTCMacHandler($context, $htcMacNormalizer));
        $chain->push(new Handlers\HTCHandler($context, $genericNormalizers));
        $chain->push(new Handlers\KDDIHandler($context, $genericNormalizers));
        $chain->push(new Handlers\KyoceraHandler($context, $genericNormalizers));

        /** @var $lgNormalizer Request\UserAgentNormalizer */
        $lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\LG());
        $chain->push(new Handlers\LGHandler($context, $lgNormalizer));

        /** @var $maemoNormalizer Request\UserAgentNormalizer */
        $maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Maemo()
        );
        $chain->push(new Handlers\MaemoHandler($context, $maemoNormalizer));
        $chain->push(
            new Handlers\MitsubishiHandler($context, $genericNormalizers)
        );
        $chain->push(new Handlers\NecHandler($context, $genericNormalizers));
        $chain->push(new Handlers\NintendoHandler($context, $genericNormalizers));
        $chain->push(
            new Handlers\PanasonicHandler($context, $genericNormalizers)
        );
        $chain->push(new Handlers\PantechHandler($context, $genericNormalizers));
        $chain->push(new Handlers\PhilipsHandler($context, $genericNormalizers));
        $chain->push(
            new Handlers\PortalmmmHandler($context, $genericNormalizers)
        );
        $chain->push(new Handlers\QtekHandler($context, $genericNormalizers));
        $chain->push(new Handlers\ReksioHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SagemHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SanyoHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SharpHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SiemensHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SkyfireHandler($context, $genericNormalizers));
        $chain->push(new Handlers\SPVHandler($context, $genericNormalizers));
        $chain->push(new Handlers\ToshibaHandler($context, $genericNormalizers));
        $chain->push(new Handlers\VodafoneHandler($context, $genericNormalizers));

        /** @var $webOSNormalizer Request\UserAgentNormalizer */
        $webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\WebOS()
        );
        $chain->push(new Handlers\WebOSHandler($context, $webOSNormalizer));
        $chain->push(
            new Handlers\FirefoxOSHandler($context, $genericNormalizers)
        );
        $chain->push(
            new Handlers\OperaMiniHandler($context, $genericNormalizers)
        );

        /**** Tablet Browsers ****/
        $chain->push(
            new Handlers\WindowsRTHandler($context, $genericNormalizers)
        );

        /**** Robots / Crawlers ****/
        $chain->push(
            new Handlers\BotCrawlerTranscoderHandler($context, $genericNormalizers)
        );

        /**** Game Consoles ****/
        $chain->push(new Handlers\XboxHandler($context, $genericNormalizers));

        /**** Desktop Browsers ****/
        /** @var $chromeNormalizer Request\UserAgentNormalizer */
        $chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Chrome()
        );
        $chain->push(new Handlers\ChromeHandler($context, $chromeNormalizer));

        /** @var $firefoxNormalizer Request\UserAgentNormalizer */
        $firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Firefox()
        );
        $chain->push(new Handlers\FirefoxHandler($context, $firefoxNormalizer));

        /** @var $msieNormalizer Request\UserAgentNormalizer */
        $msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new Request\UserAgentNormalizer\Specific\MSIE());
        $chain->push(new Handlers\MSIEHandler($context, $msieNormalizer));

        /** @var $operaNormalizer Request\UserAgentNormalizer */
        $operaNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Opera()
        );
        $chain->push(new Handlers\OperaHandler($context, $operaNormalizer));

        /** @var $safariNormalizer Request\UserAgentNormalizer */
        $safariNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Safari()
        );
        $chain->push(new Handlers\SafariHandler($context, $safariNormalizer));

        /** @var $konquerorNormalizer Request\UserAgentNormalizer */
        $konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(
            new Request\UserAgentNormalizer\Specific\Konqueror()
        );
        $chain->push(
            new Handlers\KonquerorHandler($context, $konquerorNormalizer)
        );

        /**** All other requests ****/
        $chain->push(new Handlers\CatchAllHandler($context, $genericNormalizers));

        return $chain;
    }

    /**
     * Returns a User Agent Normalizer chain containing all generic normalizers
     *
     * @return Request\UserAgentNormalizer
     */
    private static function createGenericNormalizers()
    {
        return new Request\UserAgentNormalizer(array(
             new Request\UserAgentNormalizer\Generic\UCWEB(),
             new Request\UserAgentNormalizer\Generic\UPLink(),
             new Request\UserAgentNormalizer\Generic\SerialNumbers(),
             new Request\UserAgentNormalizer\Generic\LocaleRemover(),
             new Request\UserAgentNormalizer\Generic\BlackBerry(),
             new Request\UserAgentNormalizer\Generic\YesWAP(),
             new Request\UserAgentNormalizer\Generic\BabelFish(),
             new Request\UserAgentNormalizer\Generic\NovarraGoogleTranslator(),
             new Request\UserAgentNormalizer\Generic\TransferEncoding(),
        ));
    }
}