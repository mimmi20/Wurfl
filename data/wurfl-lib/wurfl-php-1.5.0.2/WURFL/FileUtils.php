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
 * WURFL File Utilities
 * @package	WURFL
 */
class WURFL_FileUtils {
	
	/**
	 * Create a directory structure recursiveley
	 * @param string $path
	 * @param int $mode
	 */
	public static function mkdir($path, $mode=0755) {
		@mkdir($path, $mode, true);
	}
	
	/**
	 * Recursiely remove all files from the given directory NOT including the
	 * specified directory itself
	 * @param string $path Directory to be cleaned out
	 */
	public static function rmdirContents($path) {
		$files = scandir($path);
		array_shift($files); // remove '.' from array
		array_shift($files); // remove '..' from array
		
		foreach ($files as $file) {
			$file = $path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file )) {
				self::rmdir($file);
				rmdir($file);
			} else {
				unlink($file);
			}
		}	
	}
	
	/**
	 * Alias to rmdirContents()
	 * @param string $path Directory to be cleaned out
	 * @see rmdirContents()
	 */
	public static function rmdir($path) {
		self::rmdirContents($path);
	}
	
	/**
	 * Returns the unserialized contents of the given $file
	 * @param string $file filename
	 * @return mixed Unserialized data or null if file does not exist
	 */
	public static function read($file) {
		if (!is_readable($file)) return null;
		$data = @file_get_contents($file);
		if ($data === false) return null;
		$value = @unserialize($data);
		if ($value === false) return null;
		return $value;
	}
	
	/**
	 * Serializes and saves $data in the file $path and sets the last modified time to $mtime  
	 * @param string $path filename to save data in
	 * @param mixed $data data to be serialized and saved
	 * @param integer $mtime Last modified date in epoch time
	 */
	public static function write($path, $data, $mtime = 0) {
		if (!file_exists(dirname($path))) {
			self::mkdir(dirname($path), 0755, true);
		}
		if (file_put_contents($path, serialize($data), LOCK_EX )) {
			$mtime = ($mtime > 0)? $mtime: time();
			@chmod($path, 0777);
			@touch($path, $mtime);
		}
	}
	
	/**
	 * Combines given array of $strings into a proper filesystem path
	 * @param array $strings Array of (string)path members
	 * @return string Proper filesystem path 
	 */
	public static function join($strings = array()) {
		return implode(DIRECTORY_SEPARATOR, $strings);
	}
	
	/**
	 * Returns a directory for storing temporary files
	 * @return string 
	 */
	public static function getTempDir() {
		$temp_dir = ini_get('upload_tmp_dir');
		if (!$temp_dir) {
			$temp_dir = function_exists('sys_get_temp_dir')? sys_get_temp_dir(): '/tmp';
		}
		return realpath($temp_dir);
	}
	
	/**
	 * Cleans the filename by removing duplicate directory separators and normalizing them for the current OS
	 * @param string $fileName
	 * @return string
	 */
	public static function cleanFilename($fileName) {
		return preg_replace('#[/\\\]+#', DIRECTORY_SEPARATOR, $fileName);
	}
}
