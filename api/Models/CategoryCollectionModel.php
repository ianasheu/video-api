<?php
/*----------------------------*
 * 
 * Modele pour les categories
 * 
 *----------------------------*/

namespace api\Models;

use \api\Models\Database,
	\api\Models\CategoryItemModel,
	\api\Models\MovieItemModel;

class CategoryCollectionModel implements CollectionModelInterface {

	// Propriétés
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
	 */
	public static function existsProperty($property) {
		return (property_exists('\api\Models\CategoryItemModel', $property));
	}

	/*
	 * Creer une categorie
	 */
	public function create(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO ' . self::TABLE . ' (tag) VALUES (:tag);');
			$query->bindParam('tag', $content->tag);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Associer une categorie a un film
	 */
	public function createMovie(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO moviecategory (movie, category) VALUES (:movie, :category);');
			$query->bindParam('movie', $content->movie);
			$query->bindParam('category', $content->category);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Lire tous les categories
	 */
	public function readAll($orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT id, tag FROM ' . self::TABLE;
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$category = new CategoryItemModel($id, $tag);
				array_push($this->collection, $category);
			}
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire une categorie par l id
	 */
	public function readById($id, $detailed=null) : array {

		$sql = 'SELECT id, tag FROM ' . self::TABLE . ' WHERE id = :id;';
		$query = $this->db->prepare($sql);
		$query->bindParam('id', $id);
		$query->execute();

		if ($query->rowCount() > 0) {
			$row = $query->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$category = new CategoryItemModel($id, $tag);
			if ($detailed == 'true') {
				$category->movie = $this->readMovie($id);
			}
			array_push($this->collection, $category);
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire les films d une category
	 */
	public function readMovie($id, $orderby=null, $limit=null, $offset=null) : array {

		$result = array();
		$sql = 'SELECT movie.id, movie.title, movie.year, movie.rating, movie.poster, movie.allocine FROM movie, moviecategory WHERE moviecategory.movie = movie.id AND moviecategory.category = :id';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindParam('id', $id);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$mov = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($result, $mov);
			}
			return $result;
		} else {
			return array();
		}
	}

	/*
	 * Mettre a jour une categorie
	 */
	public function update(object $content) {

		try {
			$query = $this->db->prepare('UPDATE ' . self::TABLE . ' SET tag = :tag WHERE id=:id;');
			$query->bindParam('id', $content->id);
			$query->bindParam('tag', $content->tag);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}

	/*
	 * Supprimer une categorie par id
	 */
	public function deleteById($id) {

		try {
			$query = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id=:id;');
			$query->bindParam('id', $id);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}

	/*
	 * Dissocier une categorie d un film
	 */
	public function deleteMovie($movie, $category) {

		try {
			$query = $this->db->prepare('DELETE FROM moviecategory WHERE movie=:movie AND category=:category;');
			$query->bindParam('movie', $movie);
			$query->bindParam('category', $category);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}
}