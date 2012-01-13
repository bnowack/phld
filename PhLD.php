<?php

/* Set the namespace. */
namespace phld;

use \phweb\PhWeb as PhWeb;
use \phweb\Configuration as Configuration;
use \phweb\utils\StringUtils as StringUtils;
use \phweb\Exception as Exception;

/* Register the PhLD autoloader. */
spl_autoload_register(array('phld\PhLD', 'autoload'), true, true);

// Make sure the include directory is available.
defined('PHLD_DIR') || define('PHLD_DIR', rtrim(dirname(__FILE__)) . '/');


/**
 * PhLD Core (static for global access).
 * 
 * Static, acts as autoloader, singleton registry, ...
 * 
 * Logs used: autoload_error
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class PhLD {

	protected static $autoLoaded = array();

	/* Disable instantiation. */
	protected function __construct() {}

	/* Disable cloning. */
	private function __clone() {}
	
	/**
	 * Locates and loads the class file associated with the referenced class.
	 * 
	 * @param string $className 
	 */
	public static function autoload($className) {
		if (isset(self::$autoLoaded[$className])) return;
		self::$autoLoaded[$className] = true;
		$dirs = array(
			preg_replace('/phld\/$/', '', PHLD_DIR),
			PHLD_DIR,
		);
		if (defined('PHLD_INCLUDE_DIR')) {
			$dirs[] = PHLD_INCLUDE_DIR;
		}
		foreach ($dirs as $rootDir) {
			$path =	$rootDir . str_replace(array('\\', '_'), '/', $className) . '.php';
			$path = str_replace('/phld/phld/', '/phld/', $path);
			if (file_exists($path)) {
				require($path);
				break;
			}
		}
		if (!class_exists($className, false) && !interface_exists($className, false)) {
			// don't log class auto-loading to avoid circular refs
			if (preg_match('/Log$/', $className)) return;
			PhWeb::log("Could not autoload '{$className}' ('{$path}') in PhLD. {$_SERVER['REQUEST_URI']}", 'autoload_info');
		}
		else {
			PhWeb::log("Autoloaded '{$className}' ('{$path}') in PhLD.", 'autoload_info');
		}
	}
	
	/**
	 * Returns a Configuration option via $name, or the static Configuration class.
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mixed 
	 */
	public static function getConfiguration($name = '', $default = null) {
		return PhWeb::getConfiguration($name, $default);
	}
	
	/**
	 * Sets a Configuration option.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public static function setConfiguration($name, $value) {
		return PhWeb::setConfiguration($name, $value);
	}
	
	/**
	 * Returns an object instance from a local singleton registry (sort-of).
	 * 
	 * Supports multiple instances of a class through an $instanceName identifier,
	 * i.e. the instances are not necessarily singletons.
	 * 
	 * Arguments are passed to the constructor (once) at instantiation time.
	 * 
	 * @param string $className
	 * @param string $instanceName
	 * @param mixed $args
	 * @return object 
	 */
	public static function getRegistryInstance($className, $instanceName = '', $args = array()) {
		return PhWeb::getRegistryInstance($className, $instanceName, $args);
	}
	
	/**
	 * Returns a request singleton.
	 * 
	 * @return Request
	 */
	static public function getRequest() {
		return PhWeb::getRequest();
	}
	
	/**
	 * Returns a serialiser instance for the given format.
	 * 
	 * @param string $format
	 * @param string $className
	 * @return Serializer 
	 */
	static public function getSerializer($format, $className = null) {
		if (!$className) {
			$className = 'phld\\serializers\\' . StringUtils::camelCase("$format-serializer");
		}
		if (!class_exists($className, true)) {
			throw new Exception("Could not load $format serializer.");
		}
		return new $className();
	}
	
	/**
	 * Returns a parser instance for the given format.
	 * 
	 * @param string $format
	 * @param string $className
	 * @return Serializer 
	 */
	static public function getParser($format, $className = null) {
		if (!$className) {
			$className = 'phld\\parsers\\' . StringUtils::camelCase("$format-parser");
		}
		if (!class_exists($className, true)) {
			throw new Exception("Could not load $format parser.");
		}
		return new $className();
	}
	
}
