<?php
/*----------------------------*
 *
 * Modele pour les categories
 *
 *----------------------------*/

declare(strict_types=1);

namespace api\Models;

use api\Models\CollectionModelInterface,
	api\Models\Database,
	api\Models\CategoryItemModel,
	api\Models\MovieItemModel;

class CategoryCollectionModel implements CollectionModelInterface {

	/*
	 * @property string TABLE
	 * @property object $db
	 * @property array $collection
	 */
	private const TABLE = 'category';
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
	 *
	 * @param string $property
	 * @return bool
	 */
	public static function existsProperty(string $property) : bool {
		return (property_exists('api\Models\CategoryItemModel', $property));
	}

	/*
	 * Creer une categorie
	 *
	 * @param object $content
	 * @return int|bool id cree ou false
	 */
	public function create(object $content) : int|bool {

		try {
			$query = $this->db->prepare('INSERT INTO ' . self::TABLE . ' (tag) VALUES (:tag);');
			$query->bindValue('tag', $content->tag);
			$query->execute();
			return intval($this->db->lastInsertId());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Associer une categorie a un film
	 *
	 * @param object $content
	 * @return int|bool id cree ou false
	 * retourne zero si movie ou category ne sont pas des ids existants
	 */
	public function createMovie(object $content) : int|bool {

		try {
			$query = $this->db->prepare('INSERT INTO moviecategory (movie, category) VALUES (:movie, :category);');
			$query->bindValue('movie', intval($content->movie), \PDO::PARAM_INT);
			$query->bindValue('category', intval($content->category), \PDO::PARAM_INT);
			$query->execute();
			return intval($this->db->lastInsertId());

		} catch (\PDOException $exception) {
			if ($query) {
				$error = $query->errorInfo();
				if($error && isset($error[1]) && $error[1] == 1452) {
					return 0;
				}
			}
			return false;
		}
	}

	/*
	 * Lire tous les categories
	 *
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function readAll(?string $orderby=null, ?int $limit=null, ?int $offset=null) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, tag FROM ' . self::TABLE;
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$category = new CategoryItemModel(intval($id), $tag);
				array_push($this->collection, $category);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire une categorie par l id
	 *
	 * @param int $id
	 * @return array
	 */
	public function readById(int $id) : array {

		$sql = 'SELECT SQL_CALC_FOUND_ROWS id, tag FROM ' . self::TABLE . ' WHERE id = :id;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			$row = $query->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$category = new CategoryItemModel(intval($id), $tag);
			array_push($this->collection, $category);
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire les films d une category
	 *
	 * @param int $id
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function readMovie(int $id, ?string $orderby=null, ?int $limit=null, ?int $offset=null) : array {

		$result = array();
		$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id, movie.title, movie.year, movie.rating, movie.poster, movie.allocine FROM movie, moviecategory WHERE moviecategory.movie = movie.id AND moviecategory.category = :id';
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
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
				$mov = new MovieItemModel(intval($id), $title, intval($year), floatval($rating), $poster, $allocine);
				array_push($result, $mov);
			}
			return [$result, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Mettre a jour une categorie
	 * 
	 * @param object $content
	 * @return int|bool nb de modif ou false
	 */
	public function update(object $content) : int|bool {

		try {
			$query = $this->db->prepare('UPDATE ' . self::TABLE . ' SET tag = :tag WHERE id=:id;');
			$query->bindValue('id', intval($content->id), \PDO::PARAM_INT);
			$query->bindValue('tag', $content->tag);
			$query->execute();
			return intval($query->rowCount());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Supprimer une categorie par l id
	 * 
	 * @param int $id
	 * @return int|bool nb de supression ou false
	 */
	public function deleteById(int $id) : int|bool  {

		try {
			$query = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id=:id;');
			$query->bindValue('id', intval($id), \PDO::PARAM_INT);
			$query->execute();
			return intval($query->rowCount());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Dissocier une categorie d un film
	 * 
	 * @param int $movie
	 * @param int $category
	 * @return int|bool nb de supression ou false
	 */
	public function deleteMovie(int $movie, int $category) : int|bool {

		try {
			$query = $this->db->prepare('DELETE FROM moviecategory WHERE movie=:movie AND category=:category;');
			$query->bindValue('movie', intval($movie), \PDO::PARAM_INT);
			$query->bindValue('category', intval($category), \PDO::PARAM_INT);
			$query->execute();
			return intval($query->rowCount());

		} catch (\PDOException $exception) {
			return false;
		}
	}
}