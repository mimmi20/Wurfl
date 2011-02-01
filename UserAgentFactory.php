<?php
declare(ENCODING = 'iso-8859-1');
namespace TeraWurfl;

/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurfl
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */

/**
 * Evaluates the user agent using keywords, regular expressions, UserAgentMatchers and HTTP Headers
 * @package TeraWurfl
 * @see UserAgentMatcher
 *
 */
class UserAgentFactory
{
    private $_wurfl = null;
    private $_agent = '';
    
    private $_helper = null;
    
    // Constructor
    public function __construct(TeraWurfl $wurfl, $userAgent)
    {
        $this->_wurfl = $wurfl;
        $this->_agent = strtolower($userAgent);
        
        $this->_helper = new UserAgentMatchers\MatcherHelper($userAgent);
    }
    
    // Public Methods
    /**
     * Determines which UserAgentMatcher is the best fit for the incoming user agent and returns it
     * @return UserAgentMatcher
     */
    public function createUserAgentMatcher()
    {
        // $isMobile means it IS MOBILE, $isDesktop means it IS DESKTOP
        // $isMobile does NOT mean it IS DESKTOP and vica-versa
        $isMobile  = UserAgentUtils::isMobileBrowser($this->_agent);
        $isDesktop = UserAgentUtils::isDesktopBrowser($this->_agent);
        
        // Process MOBILE user agents
        if (!$isDesktop) {
            // High workload UAMs go first
            // Nokia
            if ($this->_helper->contains('nokia')) {
                return new UserAgentMatchers\Nokia($this->_wurfl, $this->_agent);
            }
            // Samsung
            if ($this->_helper->contains(array('samsung/sgh','samsung-sgh'))
                || $this->_helper->startsWith(array('sec-','samsung','sph','sgh','sch'))
                || stripos($this->_agent, 'samsung') !== false
            ) {
                
                return new UserAgentMatchers\Samsung($this->_wurfl, $this->_agent);
            }
            // Blackberry
            if (stripos($this->_agent, 'blackberry') !== false) {
                return new UserAgentMatchers\BlackBerry($this->_wurfl, $this->_agent);
            }
            // SonyEricsson
            if ($this->_helper->contains('sony')) {
                return new UserAgentMatchers\SonyEricsson($this->_wurfl, $this->_agent);
            }
            // Motorola
            if ($this->_helper->startsWith(array('mot-','moto'))
                || $this->_helper->contains('motorola')
            ) {
                return new UserAgentMatchers\Motorola($this->_wurfl, $this->_agent);
            }
            
            // Continue processing UAMs in alphabetical order
            // Alcatel
            if ($this->_helper->startsWith('alcatel')) {
                return new UserAgentMatchers\Alcatel($this->_wurfl, $this->_agent);
            }
            // Apple
            if ($this->_helper->contains(array('iphone', 'ipod', 'ipad'))) {
                return new UserAgentMatchers\Apple($this->_wurfl, $this->_agent);
            }
            // BenQ
            if ($this->_helper->startsWith('benq')) {
                return new UserAgentMatchers\BenQUser($this->_wurfl, $this->_agent);
            }
            // DoCoMo
            if ($this->_helper->startsWith('docomo')) {
                return new UserAgentMatchers\DoCoMo($this->_wurfl, $this->_agent);
            }
            // Grundig
            if ($this->_helper->startsWith('grundig')) {
                return new UserAgentMatchers\Grundig($this->_wurfl, $this->_agent);
            }
            // HTC
            if ($this->_helper->contains(array('htc', 'xv6875'))) {
                return new UserAgentMatchers\HTC($this->_wurfl, $this->_agent);
            }
            // KDDI
            if ($this->_helper->contains('kddi-')) {
                return new UserAgentMatchers\Kddi($this->_wurfl, $this->_agent);
            }
            // Kyocera
            if ($this->_helper->startsWith(array('kyocera', 'qc-', 'kwc-'))) {
                return new UserAgentMatchers\Kyocera($this->_wurfl, $this->_agent);
            }
            // LG
            if ($this->_helper->startsWith('lg')) {
                return new UserAgentMatchers\Lg($this->_wurfl, $this->_agent);
            }
            // Mitsubishi
            if ($this->_helper->startsWith('mitsu')) {
                return new UserAgentMatchers\Mitsubishi($this->_wurfl, $this->_agent);
            }
            // NEC
            if ($this->_helper->startsWith(array('nec-', 'kgt'))) {
                return new UserAgentMatchers\Nec($this->_wurfl, $this->_agent);
            }
            // Nintendo
            if ($this->_helper->contains('nintendo') || 
                // Nintendo DS: Mozilla/4.0 (compatible; MSIE 6.0; Nitro) Opera 8.50 [en]
                ($this->_helper->startsWith('mozilla/') 
                    && $this->_helper->contains('nitro') 
                    && $this->_helper->contains('opera'))
            ) {
                return new UserAgentMatchers\Nintendo($this->_wurfl, $this->_agent);
            }
            // Panasonic
            if ($this->_helper->startsWith('panasonic')) {
                return new UserAgentMatchers\Panasonic($this->_wurfl, $this->_agent);
            }
            // Pantech
            if ($this->_helper->startsWith(array('pantech', 'pt-', 'pantech', 'pg-'))) {
                return new UserAgentMatchers\Pantech($this->_wurfl, $this->_agent);
            }
            // Philips
            if ($this->_helper->startsWith('philips')) {
                return new UserAgentMatchers\Philips($this->_wurfl, $this->_agent);
            }
            // Portalmmm
            if ($this->_helper->startsWith('portalmmm')) {
                return new UserAgentMatchers\Portalmmm($this->_wurfl, $this->_agent);
            }
            // Qtek
            if ($this->_helper->startsWith('qtek')) {
                return new UserAgentMatchers\Qtek($this->_wurfl, $this->_agent);
            }
            // Sagem
            if ($this->_helper->startsWith('sagem')) {
                return new UserAgentMatchers\Sagem($this->_wurfl, $this->_agent);
            }
            // Sanyo
            if ($this->_helper->startsWith('sanyo') 
                || $this->_helper->contains('mobilephone')
            ) {
                return new UserAgentMatchers\Sanyo($this->_wurfl, $this->_agent);
            }
            // Sharp
            if ($this->_helper->startsWith('sharp')) {
                return new UserAgentMatchers\Sharp($this->_wurfl, $this->_agent);
            }
            // Siemens
            if ($this->_helper->startsWith('sie-')) {
                return new UserAgentMatchers\Siemens($this->_wurfl, $this->_agent);
            }
            // SPV
            if ($this->_helper->contains('spv')) {
                return new UserAgentMatchers\Spv($this->_wurfl, $this->_agent);
            }
            // Toshiba
            if ($this->_helper->startsWith('toshiba')) {
                return new UserAgentMatchers\Toshiba($this->_wurfl, $this->_agent);
            }
            // Vodafone
            if ($this->_helper->startsWith('vodafone')) {
                return new UserAgentMatchers\Vodafone($this->_wurfl, $this->_agent);
            }
            
            // Process mobile browsers after mobile devices
            // Android
            if($this->_helper->contains( 'android')){
                return new UserAgentMatchers\Android($this->_wurfl, $this->_agent);
            }
            // Opera Mini
            if ($this->_helper->contains(array('opera mini', 'opera mobi'))) {
                return new UserAgentMatchers\OperaMini($this->_wurfl, $this->_agent);
            }
            // Windows CE
            if ($this->_helper->contains( 'mozilla/') 
                && $this->_helper->contains( 'windows ce')
            ) {
                return new UserAgentMatchers\WindowsCe($this->_wurfl, $this->_agent);
            }
        } // End if(!$isDesktop)

        // Process Robots (Web Crawlers and the like)
        if (UserAgentUtils::isRobot($this->_agent)) {
            return new UserAgentMatchers\Bot($this->_wurfl, $this->_agent);
        }
        
        // Process NON-MOBILE user agents        
        if (!$isMobile) {
            // MSIE
            if ($this->_helper->startsWith('mozilla') 
                && $this->_helper->contains( 'msie')
                && !$this->_helper->contains( array('opera', 'armv', 'moto', 'brew'))
            ) {
                return new UserAgentMatchers\Msie($this->_wurfl, $this->_agent);
            }
            // Firefox
            if ($this->_helper->contains('firefox') 
                && !$this->_helper->contains(array('sony', 'novarra', 'opera'))
            ) {
                return new UserAgentMatchers\Firefox($this->_wurfl, $this->_agent);
            }
            // Chrome
            if ($this->_helper->contains('chrome')) {
                return new UserAgentMatchers\Chrome($this->_wurfl, $this->_agent);
            }
            // Konqueror
            if ($this->_helper->contains('konqueror')) {
                return new UserAgentMatchers\Konqueror($this->_wurfl, $this->_agent);
            }
            // Opera
            if ($this->_helper->contains('opera')) {
                return new UserAgentMatchers\Opera($this->_wurfl, $this->_agent);
            }
            // Safari
            if ($this->_helper->startsWith('mozilla') 
                && $this->_helper->contains('safari')
            ) {
                return new UserAgentMatchers\Safari($this->_wurfl, $this->_agent);
            }
            // AOL
            if ($this->_helper->contains( array('aol', 'america online')) 
                || $this->_helper->contains('aol 9')
            ) {
                return new UserAgentMatchers\Aol($this->_wurfl, $this->_agent);
            }
        }
        
        // Nothing has matched so we will have to use the CatchAllUserAgentMatcher
        return new UserAgentMatchers\CatchAll($this->_wurfl, $this->_agent);
    }
    
    public static function userAgentType(TeraWurfl $wurfl, $userAgent)
    {
        $obj = self::createUserAgentMatcher($wurfl, $userAgent);
        return $obj->tableSuffix();
    }
}
