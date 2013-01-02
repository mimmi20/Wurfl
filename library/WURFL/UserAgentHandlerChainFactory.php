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
 * @category   WURFL
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Manages the creation and instatiation of all User Agent Handlers and Normalizers and provides a factory for creating User Agent Handler Chains
 * @package	WURFL
 * @see WURFL_UserAgentHandlerChain
 */
class WURFL_UserAgentHandlerChainFactory {

	/**
	 * @var WURFL_UserAgentHandlerChain
	 */
	private static $_userAgentHandlerChain = null;

	/**
	 * Create a WURFL_UserAgentHandlerChain from the given $context
	 * @param WURFL_Context $context
	 * @return WURFL_UserAgentHandlerChain
	 */
	public static function createFrom(WURFL_Context $context) {
		$cached_data = $context->cacheProvider->load('UserAgentHandlerChain');
		if ($cached_data !== null) {
			self::$_userAgentHandlerChain = @unserialize($cached_data);
		}
		if (!(self::$_userAgentHandlerChain instanceof WURFL_UserAgentHandlerChain)) {
			self::init($context);
			$context->cacheProvider->save('UserAgentHandlerChain', serialize(self::$_userAgentHandlerChain), 3600);
		}
		return self::$_userAgentHandlerChain;
	}

	/**
	 * Initializes the factory with an instance of all possible WURFL_Handlers_Handler objects from the given $context
	 * @param WURFL_Context $context
	 */
	static private function init(WURFL_Context $context) {

		self::$_userAgentHandlerChain = new WURFL_UserAgentHandlerChain();

		$genericNormalizers = self::createGenericNormalizers();
		
		/**** Java Midlets ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_JavaMidletHandler($context, $genericNormalizers));
		
		/**** Smart TVs ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SmartTVHandler($context, $genericNormalizers));
		
		/**** Mobile devices ****/
		$kindleNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Kindle());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_KindleHandler($context, $kindleNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_LGUPLUSHandler($context, $genericNormalizers));
		
		/**** Mobile platforms ****/
		$androidNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Android());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_AndroidHandler($context, $androidNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_AppleHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_WindowsPhoneDesktopHandler($context, $genericNormalizers));
		$winPhoneNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_WindowsPhone());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_WindowsPhoneHandler($context, $winPhoneNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_NokiaOviBrowserHandler($context, $genericNormalizers));
		
		/**** High workload mobile matchers ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_NokiaHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SamsungHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_BlackBerryHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SonyEricssonHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_MotorolaHandler($context, $genericNormalizers));
		
		/**** Other mobile matchers ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_AlcatelHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_BenQHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_DoCoMoHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_GrundigHandler($context, $genericNormalizers));
		$htcMacNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_HTCMac());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_HTCMacHandler($context, $htcMacNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_HTCHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_KDDIHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_KyoceraHandler($context, $genericNormalizers));
		$lgNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_LG());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_LGHandler($context, $lgNormalizer));
		$maemoNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Maemo());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_MaemoHandler($context, $maemoNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_MitsubishiHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_NecHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_NintendoHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_PanasonicHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_PantechHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_PhilipsHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_PortalmmmHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_QtekHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_ReksioHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SagemHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SanyoHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SharpHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SiemensHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SPVHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_ToshibaHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_VodafoneHandler($context, $genericNormalizers));
		$webOSNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_WebOS());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_WebOSHandler($context, $webOSNormalizer));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_OperaMiniHandler($context, $genericNormalizers));
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_WindowsRTHandler($context, $genericNormalizers));
		
		/**** Robots / Crawlers ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_BotCrawlerTranscoderHandler($context, $genericNormalizers));
		
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_XboxHandler($context, $genericNormalizers));
		
		/**** Desktop Browsers ****/
		$chromeNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Chrome());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_ChromeHandler($context, $chromeNormalizer));
		
		$firefoxNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Firefox());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_FirefoxHandler($context, $firefoxNormalizer));
		
		$msieNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_MSIE());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_MSIEHandler($context, $msieNormalizer));
		
		$operaNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Opera());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_OperaHandler($context, $operaNormalizer));
		
		$safariNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Safari());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_SafariHandler($context, $safariNormalizer));
		
		$konquerorNormalizer = $genericNormalizers->addUserAgentNormalizer(new WURFL_Request_UserAgentNormalizer_Specific_Konqueror());
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_KonquerorHandler($context, $konquerorNormalizer));
		
		
		/**** All other requests ****/
		self::$_userAgentHandlerChain->addUserAgentHandler(new WURFL_Handlers_CatchAllHandler($context, $genericNormalizers));

	}

	/**
	 * Returns a User Agent Normalizer chain containing all generic normalizers
	 * @return WURFL_Request_UserAgentNormalizer
	 */
	private static function createGenericNormalizers() {
		return new WURFL_Request_UserAgentNormalizer(array(
			new WURFL_Request_UserAgentNormalizer_Generic_UPLink(),
			new WURFL_Request_UserAgentNormalizer_Generic_BlackBerry(),
			new WURFL_Request_UserAgentNormalizer_Generic_YesWAP(),
			new WURFL_Request_UserAgentNormalizer_Generic_BabelFish(),
			new WURFL_Request_UserAgentNormalizer_Generic_SerialNumbers(),
			new WURFL_Request_UserAgentNormalizer_Generic_NovarraGoogleTranslator(),
			new WURFL_Request_UserAgentNormalizer_Generic_LocaleRemover(),
			new WURFL_Request_UserAgentNormalizer_Generic_UCWEB(),
		));
	}
}