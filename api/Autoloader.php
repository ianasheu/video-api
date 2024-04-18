<?php
/*------------------------------------*
 * 
 * Chargement automatique des classes
 * 
 *------------------------------------*/

namespace api;

final class Autoloader {
	public static function register() {
		spl_autoload_register([
			__CLASS__,
			'autoload'
		]);
	}

	private static function autoload($class) {
		$class = str_replace(__NAMESPACE__ . '\\', '', $class);
		$class = str_replace('\\', '/', $class);
		$file = __DIR__ . '/' . $class . '.php';
		if (file_exists($file)) {
			require_once $file;
		} else {
			throw new \Exception('Class not found');
		}
	}
}