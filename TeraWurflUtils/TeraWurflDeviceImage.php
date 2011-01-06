<?php
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
 * Finds an image for devices detected with Tera-WURFL
 * @package TeraWurflUtils
 * 
 */
class TeraWurflDeviceImage {
	
	protected $baseURL;
	protected $imagesDirectory;
	protected $wurfl;
	protected $deviceID;
	protected $imageExt = '.gif';
	protected $image;
	protected $descend = true;
	
	/**
	 * Creates a new TeraWurflDeviceImage
	 * @param TeraWurfl The instance of TeraWurfl (or TeraWurflRemoteClient) that detected the device
	 * @return void
	 */
	public function __construct(&$wurfl){
		$this->wurfl = $wurfl;
		$this->deviceID = ($this->wurfl->getDeviceCapability('actual_root_device'))? $this->wurfl->getDeviceCapability('actual_root_device'): false;
		$this->baseURL = '';
		$this->imagesDirectory = dirname(__FILE__) . '/device_pix/';
	}
	/**
	 * Sets the base URL of the device images
	 * Must end with "/".
	 * @param string Web-accessible location of the device images  (e.g. "http://domain.com/device_pix/" or "../device_pix/")
	 * @return void
	 */
	public function setBaseURL($baseURL){
		$this->baseURL = $baseURL;
	}
	/**
	 * Sets the local directoy of the device images on the filesystem
	 * @param string Local filesystem directory where the device images are located (e.g. "C:/device_pix/" or "../../device_pix/")
	 * @return void
	 */
	public function setImagesDirectory($dir){
		$this->imagesDirectory = $dir;
	}
	/**
	 * If you set the BaseURL, returns the path and filename of the device image (http://domain.com/device_pix/apple_iphone_ver1.gif),
	 * otherwise returns only the filename (apple_iphone_ver1.gif)
	 * @return string Device image filename
	 */
	public function getImage(){
		if(is_null($this->image)) $this->setImage();
		return $this->image;
	}
	/**
	 * Set to false to prevent the image searching function from looking through the device's parent devices to find 
	 * a very similar device image if the exact device image is not found.    
	 * @param bool false prevents using the device image from a different version of the device
	 * @return void
	 */
	public function setDescendToFindImage($descend){
		$this->descend = (bool)$descend;
	}
	/**
	 * Sets the internal $this->image var with the complete path to the device image
	 * @return void
	 */
	protected function setImage(){
		if($this->deviceID === false){
			$this->image = null;
			return;
		}
		if(!file_exists($this->imagesDirectory)){
			$realpath = @realpath($this->imagesDirectory);
			if(!$realpath){
				if($this->imagesDirectory[0]=='.'){
					throw new Exception("Error: the local images directory was specified as a relative path ($this->imagesDirectory), but could not be resolved.  Current directory: ".getcwd());
					exit(1);
				}else{
					throw new Exception("Error: the local images directory specified does not exist: ".$this->imagesDirectory);
					exit(1);
				}
			}
		}
		if(!$this->imageExists($this->deviceID)){
			if($this->descend){
				// Check fall back tree for an alternate image, starting at the current device, working back to generic
				foreach(array_reverse(explode(',',$this->wurfl->capabilities['tera_wurfl']['fall_back_tree'])) as $parentID){
					if($this->imageExists($parentID)){
						$this->image = $this->baseURL . $parentID . $this->imageExt;
						return;
					}
				}
			}
			$this->image = null;
			return;
		}
		$this->image = $this->baseURL . $this->deviceID . $this->imageExt;
	}
	/**
	 * Check if a device image exists for the given deviceID
	 * @param string Device ID (WURFL ID)
	 * @return bool Device image exists
	 */
	protected function imageExists($deviceID){
		return file_exists(realpath($this->imagesDirectory) . DIRECTORY_SEPARATOR . $deviceID . $this->imageExt);
	}
	public function __toString(){
		return $this->getImage();
	}
}