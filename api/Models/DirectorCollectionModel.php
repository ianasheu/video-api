<?php
/*------------------------------*
 *
 * Modele pour les realisateurs
 *
 *------------------------------*/

namespace api\Models;

use \api\Models\Database,
	\api\Models\DirectorItemModel,
	\api\Models\MovieItemModel;

class DirectorCollectionModel implements CollectionModelInterface {

	// Propriétés
	private const TABLE = 'director';
	private object $db;
	private array $collection;

	/*
	 * Constructeur
	 */
	public function __construct() {
		$this->db = Database::getConnection();
		$this->collection = array();
	}

	/*
	 * Evalue l existence d une propriete dans la classe item associee
	 */
	public static function existsProperty($property) {
		return (property_exists('\api\Models\DirectorItemModel', $property));
	}

	/*
	 * Creer un realisateur
	 */
	public function create(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO ' . self::TABLE . ' (name, country) VALUES (:name, :country);');
			$query->bindValue('name', $content->name);
			if (!isset($content->country)) $content->country = null;
			$query->bindValue('country', $content->country);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Associer un realisateur a un film
	 */
	public function createMovie(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO moviedirector (movie, director) VALUES (:movie, :director);');
			$query->bindValue('movie', $content->movie);
			$query->bindValue('director', $content->director);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Lire tous les realisateurs
	 */
	public function readAll($orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, name, country FROM ' . self::TABLE;
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby . ' ASC' : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$director = new DirectorItemModel($id, $name, $country);
				array_push($this->collection, $director);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array();
		}
	}

	/*
	 * Lire un realisateur par l id
	 */
	public function readById($id, $detailed=null) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, name, country FROM ' . self::TABLE . ' WHERE id = :id;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			$row = $query->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$director = new DirectorItemModel($id, $name, $country);
			if ($detailed == 'true') {			
				list($director->movie, $count_movie) = $this->readMovie($id, 'year');
			}
			array_push($this->collection, $director);
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array();
		}
	}

	/*
	 * Lire les films d un realisateur
	 */
	public function readMovie($id, $orderby=null, $limit=null, $offset=null) : array {

		$result = array();
		$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id, movie.title, movie.year, movie.rating, movie.poster, movie.allocine FROM movie, moviedirector WHERE moviedirector.movie = movie.id AND moviedirector.director = :id';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby . ' ASC' : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$mov = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($result, $mov);
			}
			return [$result, Database::getRowsCount()];
		} else {
			return array();
		}
	}

	/*
	 * Lire des realisateurs par nom
	 */
	public function readByName($name, $orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, name, country FROM ' . self::TABLE . ' WHERE name LIKE :name';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby . ' ASC' : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('name', $name);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$director = new DirectorItemModel($id, $name, $country);
				array_push($this->collection, $director);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array();
		}
	}

	/*
	 * Lire des realisateurs par pays
	 */
	public function readByCountry($country, $orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, name, country FROM ' . self::TABLE . ' WHERE country LIKE :country';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby . ' ASC' : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('country', $country);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$director = new DirectorItemModel($id, $name, $country);
				array_push($this->collection, $director);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array();
		}
	}

	/*
	 * Mettre a jour un realisateur
	 */
	public function update(object $content) {

		try {
			$query = $this->db->prepare('UPDATE ' . self::TABLE . ' SET name = :name, country = :country WHERE id=:id;');
			$query->bindValue('id', $content->id);
			$query->bindValue('name', $content->name);
			if (!isset($content->country)) {
				$content->country = null;
			}
			$query->bindValue('country', $content->country);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}

	/*
	 * Supprimer un realisateur par id
	 */
	public function deleteById($id) {

		try {
			$query = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id=:id;');
			$query->bindValue('id', $id);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}

	/*
	 * Dissocier un realisateur d un film
	 */
	public function deleteMovie($movie, $director) {

		try {
			$query = $this->db->prepare('DELETE FROM moviedirector WHERE movie=:movie AND director=:director;');
			$query->bindValue('movie', $movie);
			$query->bindValue('director', $director);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}
}