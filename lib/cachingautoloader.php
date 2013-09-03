<?php
/**
 * Copyright (c) 2013 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC;

require_once __DIR__ . '/autoloader.php';

class CachingAutoloader extends Autoloader {
	protected $memoryCache = null;
	protected $constructingMemoryCache = true; // hack to prevent recursion

	public function findClass($class) {
		// Does this PHP have an in-memory cache? We cache the paths there
		if ($this->constructingMemoryCache && !$this->memoryCache) {
			$this->constructingMemoryCache = false;
			$this->memoryCache = \OC\Memcache\Factory::createLowLatency('Autoloader');
		}
		if ($this->memoryCache) {
			$path = $this->memoryCache->get($class);
			if (is_string($path)) {
				return $path;
			}
		}

		// Use the normal class loading path
		$path = parent::findClass($class);
		if (is_string($path)) {
			// Save in our memory cache
			if ($this->memoryCache) {
				$this->memoryCache->set($class, $path, 60); // cache 60 sec
			}
			return $path;
		}
		return false;
	}
}
