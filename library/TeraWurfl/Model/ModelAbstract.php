<?php
declare(ENCODING = 'utf-8');
namespace TeraWurfl\Model;

/**
 * abstrakte Basis-Klasse für alle Models
 *
 * PHP version 5
 *
 * @category  CreditCalc
 * @package   Db
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2007-2010 Unister GmbH
 * @version   SVN: $Id: ModelAbstract.php 24 2011-02-01 20:55:24Z tmu $
 */

/**
 * abstrakte Basis-Klasse für alle Models
 *
 * @category  CreditCalc
 * @package   Db
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2007-2010 Unister GmbH
 * @abstract
 */
abstract class ModelAbstract extends \Zend\Db\Table\AbstractTable
{
    /**
     * @var SF_Model_Cache_Abstract
     */
    protected $_cache;

    /**
     * @var array cache options
     */
    protected $_cacheOptions = array();

    /**
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * @var \Zend\Log\Logger
     */
    protected $_logger = null;
    
    /**
     * holds the data about the actual record
     *
     * @var \Zend\Db\Table\Row
     */
    protected $_data = null;
    
    protected $_tableprefix = 'terawurfl';

    /**
     * Konstruktor
     *
     * @param array $config the config
     *
     * @return void
     * @access public
     */
    public function __construct($config = array())
    {
        $this->_name = $this->_tableprefix . $this->_name;
        
        parent::__construct($config);
        
        $this->_db = \Zend\Registry::get('wurfldb');

        $this->_config = new \Zend\Config\Config($this->getActionController()->getInvokeArg('bootstrap')->getOptions());
        $this->_logger = \Zend\Registry::get('log');
    }

    /**
     * Set the cache options
     *
     * @param array $options
     */
    public function setCacheOptions(array $options)
    {
        $this->_cacheOptions = $options;
    }

    /**
     * Get the cache options
     *
     * @return array
     */
    public function getCacheOptions()
    {
        if (empty($this->_cacheOptions)) {
            $this->_config = new \Zend\Config\Config($this->getActionController()->getInvokeArg('bootstrap')->getOptions());
            $modelConfig = $config->modelcache;

            $this->_cacheOptions = array(
                'frontend'        => $modelConfig->frontend,
                'backend'         => $modelConfig->backend,
                'frontendOptions' => $modelConfig->front->toArray(),
                'backendOptions'  => $modelConfig->back->toArray()
            );
        }
        return $this->_cacheOptions;
    }

    /**
     * Query the cache
     *
     * @param string $tagged The tag to save data to
     *
     * @return Crdit\Core\Model\Cache|Crdit\Core\Model\ModelAbstract
     */
    public function getCached($tagged = null)
    {
        $this->_config = new \Zend\Config\Config($this->getActionController()->getInvokeArg('bootstrap')->getOptions());

        if ($config->modelcache->enable) {
            if (null === $this->_cache) {
                $this->_cache = new Cache(
                    $this,
                    $this->getCacheOptions()
                );

                $this->_cache->setTagged($tagged);
            }

            return $this->_cache;
        } else {
            /*
             * the cache is disabled
             * -> use the model directly
             */
            return $this;
        }
    }

    /**
     * Get the cache instance, configure a new instance
     * if one not present.
     *
     * @return Zend_Cache
     */
    public function getCache()
    {
        $config = \Zend\Registry::get('_config');

        if ($config->modelcache->enable) {
            return $this->_cache->getCache();
        } else {
            return $this;
        }
    }

    /**
     * Clean cache entries
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used)
     * 'matchingTag'    => remove cache entries matching all given tags
     *                     ($tags can be an array of strings or a single string)
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)
     * 'matchingAnyTag' => remove cache entries matching any given tags
     *                     ($tags can be an array of strings or a single string)
     *
     * @param  string       $mode
     * @param  array|string $tags
     * @throws Zend_Cache_Exception
     * @return boolean True if ok
     */
    public function clean($mode = 'all', $tags = array())
    {
        $this->_config = new \Zend\Config\Config($this->getActionController()->getInvokeArg('bootstrap')->getOptions());

        if ($config->modelcache->enable) {
            return $this->_cache->getCache()->clean($mode, $tags);
        } else {
            return $this;
        }
    }
    
    public function getTablePrefix()
    {
        return $this->_tableprefix;
    }
}