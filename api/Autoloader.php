<?php
/*------------------------------------*
 * 
 * Chargement automatique des classes
 * 
 *------------------------------------*/

declare(strict_types=1);

namespace api;

final class Autoloader {

	/*
	 * enregistre la methode d autoload
	 */
	public static function register() {
		spl_autoload_register([
			__CLASS__,
			'autoload'
		]);
	}

	/*
	 * charge la classe
	 *
	 * @param string $class
	 */
	private static function autoload($class) : void {
		$class = str_replace(__NAMESPACE__ . '\\', '', $class);
		$class = str_replace('\\', '/', $class);
		$file = __DIR__ . "/{$class}.php";
		if (!file_exists($file)) {
			throw new \Exception("Class {$class} not found");
		}
		require_once $file;
	}
}