<?php
require_once realpath(dirname(__FILE__).'/TeraWurflLoader.php');
class TeraWurflUpdater {
	
	const CONTEXT_WEB = 'web';
	const CONTEXT_CLI = 'cli';
	
	const SOURCE_LOCAL = 'local';
	const SOURCE_REMOTE = 'remote';
	
	/**
	 * @var TeraWurflLoader
	 */
	public $loader;
	/**
	 * @var TeraWurflUpdateDownloader
	 */
	public $downloader;
	/**
	 * @var TeraWurfl
	 */
	protected $wurfl;
	protected $source;
	protected $time_limit = 1200;
	protected $verbose = false;
	
	public function __construct(TeraWurfl $wurfl, $update_source) {
		if(TeraWurflConfig::$OVERRIDE_MEMORY_LIMIT) ini_set("memory_limit",TeraWurflConfig::$MEMORY_LIMIT);
		$this->source = $update_source;
		$this->wurfl = $wurfl;
		$this->checkDb();
		$this->checkLogFile();
		$this->downloader = new TeraWurflUpdateDownloader($this->wurfl);
	}
	
	public function setVerbose($verbose=true) {
		$this->verbose = $verbose;
		$this->downloader->verbose = $this->verbose;
	}
	
	public function setTimeLimit($time_limit) {
		$this->time_limit = $time_limit;
	}
	
	public function isUpdateAvailable() {
		return $this->downloader->isUpdateAvailable();
	}
	
	public function update() {
		if ($this->source == self::SOURCE_REMOTE) {
			$this->downloader->downloadUpdate();
		}
		$this->loader = new TeraWurflLoader($this->wurfl);
		return $this->loader->load();
	}
	
	protected function checkDb() {
		if ($this->wurfl->db->connected !== true) {
			throw new TeraWurflException("Cannot connect to database: ".$this->wurfl->db->errors[0]);
		}
	}
	
	protected function checkLogFile() {
		$logfile = $this->wurfl->rootdir.TeraWurflConfig::$DATADIR.TeraWurflConfig::$LOG_FILE;
		if (!file_exists($logfile)) {
			 if (!is_writable($this->wurfl->rootdir.TeraWurflConfig::$DATADIR)) {
			 	throw new TeraWurflException("Logfile does not exist and it cannot be created because the data dir is not writable");
			 }
			 if (!touch($logfile)) {
			 	throw new TeraWurflException("Unable to create logfile");
			 }
		}
	}
}

class TeraWurflUpdateDownloader {
	
	protected $wurfl_file_zipped;
	protected $wurfl_file_xml;
	public $download_url;
	public $verbose = false;
	
	public $compressed_size;
	public $uncompressed_size;
	public $download_speed;
	public $download_time;
	
	/**
	 * @var TeraWurfl
	 */
	protected $wurfl;
	
	public function __construct(TeraWurfl $wurfl) {
		$this->wurfl = $wurfl;
		$this->wurfl_file_zipped = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE.".zip";
		$this->wurfl_file_xml = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE;
		$this->download_url = TeraWurflConfig::$WURFL_DL_URL;
		$twversion = $this->wurfl->release_branch . " " . $this->wurfl->release_version;
		@ini_set('user_agent', "WURFL/Database API $twversion");
		$this->checkPermissions();
	}
	
	public function isUpdateAvailable() {
		$context = stream_context_create(array('http' => array('method' => 'HEAD')));
		@file_get_contents($this->download_url, false, $context);
		if (isset($http_response_header) && is_array($http_response_header)) {
			foreach ($http_response_header as $header) {
				if (preg_match('/^Last-Modified: (.*)$/i', $header, $matches)) {
					$local_date = @filemtime($this->wurfl_file_zipped);
					$remote_date = strtotime($matches[1]);
					if ($remote_date <= $local_date) {
						if ($this->verbose) {
							echo "Local WURFL Date: ".date('r', $local_date)."\n";
							echo "Remote WURFL Date: ".date('r', $remote_date)."\n";
							echo "Your WURFL Data is up to date.\n";
						}
						return false;
					} else {
						return true;
					}
				}
			}
		}
		throw new TeraWurflException('Unable to use HEAD request to check for updated WURFL');
	}
	
	public function downloadUpdate() {
		if ($this->verbose === true) echo "Downloading WURFL from $this->download_url ...\n\n";
		
		$download_start = microtime(true);
		if (!$gzdata = @file_get_contents($this->download_url)) {
			if (isset($http_response_header) && is_array($http_response_header)) {
				list($version, $status_code, $msg) = explode(' ', $http_response_header[0], 3);
				$status_code = (int)$status_code;
			} else {
				$status_code = 404;
				$msg = "File not found";
			}
			throw new TeraWurflUpdateDownloaderException("Unable to download WURFL file: HTTP Error $status_code: $msg", $status_code);
		}
		$this->download_time = microtime(true) - $download_start;
		file_put_contents($this->wurfl_file_zipped, $gzdata);
		$gzdata = null;
		$this->compressed_size = filesize($this->wurfl_file_zipped);
		// Try to use ZipArchive, included from 5.2.0
		if (class_exists("ZipArchive", false)) {
			$zip = new ZipArchive();
			if ($zip->open(str_replace('\\', '/', $this->wurfl_file_zipped)) === true) {
				$zip->extractTo(str_replace('\\' ,'/', dirname($this->wurfl_file_xml)), array('wurfl.xml'));
				$zip->close();
			} else {
				throw new TeraWurflException("Error: Unable to extract wurfl.xml from downloaded archive: $this->wurfl_file_zipped");
			}
		} else {
			system("gunzip $this->wurfl_file_zipped");
		}
		$this->uncompressed_size = filesize($this->wurfl_file_xml);
		$this->download_speed = WurflSupport::formatBitrate(filesize($this->wurfl_file_zipped), $this->download_time);
		if ($this->verbose === true) {
			$nice_size = WurflSupport::formatBytes($this->uncompressed_size)." [".WurflSupport::formatBytes($this->compressed_size)." compressed]";
			echo "done ($this->wurfl_file_xml: $nice_size)\nDownloaded in $this->download_time sec @ $this->download_speed \n\n";
			flush();
		}
		usleep(50000);
	}
	
	protected function checkPermissions() {
		if(!file_exists($this->wurfl_file_zipped) && !is_writable($this->wurfl->rootdir.TeraWurflConfig::$DATADIR)){
			$this->wurfl->toLog("Cannot write to data directory (permission denied)",LOG_ERR);
			throw new TeraWurflException("Fatal Error: Cannot write to data directory (permission denied). (".$this->wurfl->rootdir.TeraWurflConfig::$DATADIR.")\n\nPlease make the data directory writable by the user or group that runs the webserver process, in Linux this command would do the trick if you're not too concerned about security: chmod -R 777 ".$this->wurfl->rootdir.TeraWurflConfig::$DATADIR);
		}
		if(file_exists($this->wurfl_file_zipped) && !is_writable($this->wurfl_file_zipped)){
			$this->wurfl->toLog("Cannot overwrite WURFL file (permission denied)",LOG_ERR);
			throw new TeraWurflException("Fatal Error: Cannot overwrite WURFL file (permission denied). (".$this->wurfl->rootdir.TeraWurflConfig::$DATADIR.")\n\nPlease make the data directory writable by the user or group that runs the webserver process, in Linux this command would do the trick if you're not too concerned about security: chmod -R 777 ".$this->wurfl->rootdir.TeraWurflConfig::$DATADIR);
		}
	}
} 