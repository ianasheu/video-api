<?php
/*---------------------------------------------*
 *
 * Singleton de connexion a la base de donnees
 *
 *---------------------------------------------*/

namespace api\Models;

require_once ROOT_PATH . '/Models/database_config.php';

final class Database {
	// Propriétés
	private static object $connection;

	/*
	 * Private Constructeur
	 */
	private function __construct() {}

	/*
	 * Private Cloneur
	 */
	private function __clone() {}

	/*
	 * Deserialiseur leve une exception
	 */
	public function __wakeup() {
		throw new \Exception('Cannot unserialize singleton');
	}

	/*
	 * Getter de la connection
	 */
	public static function getConnection() : object {

		if (!isset(self::$connection) || is_null(self::$connection)) {
			try {
				self::$connection = new \PDO('mysql:host=' . DB_HOST . '; dbname=' . DB_DATABASE . '; charset=utf8mb4;', DB_USERNAME, DB_PASSWORD);
				self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				self::$connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
				self::$connection->setAttribute(\PDO::MYSQL_ATTR_FOUND_ROWS, true);
				self::$connection->exec('set names utf8mb4');
				if (mysqli_connect_errno()) {
					throw new \Exception('Erreur de connection a la base de donnees.');
				}

			} catch (PDOException $exception) {
				throw new \Exception($exception->getMessage());
			}
		}
		return self::$connection;
	}

	public static function getRowsCount() {
		$row = self::$connection->query('SELECT FOUND_ROWS();');
		$count = $row->fetchColumn();
		return intval($count);
	}
}