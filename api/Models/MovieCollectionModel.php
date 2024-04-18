<?php
/*-----------------------*
 * 
 * Modele pour les films
 * 
 *-----------------------*/

namespace api\Models;

use \api\Models\Database,
	\api\Models\MovieItemModel,
	\api\Models\DirectorItemModel,
	\api\Models\CategoryItemModel;

class MovieCollectionModel implements CollectionModelInterface {

	// Propriétés
	private const TABLE = 'movie';
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
		return (property_exists('\api\Models\MovieItemModel', $property));
	}

	/*
	 * Creer un film
	 */
	public function create(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO ' . self::TABLE . ' (title, year, rating, poster, allocine) VALUES (:title, :year, :rating, :poster, :allocine);');
			$query->bindValue('title', $content->title);
			if (!isset($content->year)) $content->year = null;
			if (!isset($content->rating)) $content->rating = null;
			if (!isset($content->poster)) $content->poster = null;
			if (!isset($content->allocine)) $content->allocine = null;
			$query->bindValue('year', $content->year);
			$query->bindValue('rating', $content->rating);
			$query->bindValue('poster', $content->poster);
			$query->bindValue('allocine', $content->allocine);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Associer un film a un realisateur
	 */
	public function createDirector(object $content) {

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
	 * Associer un film a une categorie
	 */
	public function createCategory(object $content) {

		try {
			$query = $this->db->prepare('INSERT INTO moviecategory (movie, category) VALUES (:movie, :category);');
			$query->bindValue('movie', $content->movie);
			$query->bindValue('category', $content->category);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($this->db->lastInsertId());
	}

	/*
	 * Lire tous les films
	 */
	public function readAll($orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE;
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
				$movie = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($this->collection, $movie);
			}
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire un film par l id
	 */
	public function readById($id, $detailed=null) : array {

		$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE id = :id;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', $id);
		$query->execute();

		if ($query->rowCount() > 0) {
			$row = $query->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$movie = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
			if ($detailed == 'true') {
				$movie->director = $this->readDirector($id);
				$movie->category = $this->readCategory($id);
			}
			array_push($this->collection, $movie);
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire les realisateurs d un film
	 */
	public function readDirector($id) : array {

		$result = array();
		$sql = 'SELECT director.id, director.name, director.country FROM director, moviedirector WHERE moviedirector.director = director.id AND moviedirector.movie = :id;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', $id);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$dir = new DirectorItemModel($id, $name, $country);
				array_push($result, $dir);
			}
			return $result;
		} else {
			return array();
		}
	}

	/*
	 * Lire les categories d un film
	 */
	public function readCategory($id) : array {

		$result = array();
		$sql = 'SELECT category.id, category.tag FROM category, moviecategory WHERE moviecategory.category = category.id AND moviecategory.movie = :id;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', $id);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$cat = new CategoryItemModel($id, $tag);
				array_push($result, $cat);
			}
			return $result;
		} else {
			return array();
		}
	}

	/*
	 * Lire des films par titre
	 */
	public function readByTitle($title, $orderby=null, $limit=null, $offset=null, $simplesearch=false) : array {

		if ($simplesearch) {
			$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE title LIKE :title';
		} else {
			$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE;
			$title = trim($title, '%');
			$words = explode('%', $title);
			$sql .= " WHERE title LIKE '%".$words[0]."%'";
			if (count($words)>1) {
				for ($i=1; $i<count($words); $i++) {
					$sql .= " AND title LIKE '%" . $words[$i] . "%'";
				}
			}
		}
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if ($simplesearch) $query->bindValue('title', $title);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($this->collection, $movie);
			}
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire des films par annee
	 */
	public function readByYear($year, $orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE year LIKE :year';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('year', $year);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($this->collection, $movie);
			}
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Lire des films par note
	 */
	public function readByRating($rating, $orderby=null, $limit=null, $offset=null) : array {

		$sql = 'SELECT id, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE rating LIKE :rating';
		$sql = ($orderby ? $sql . ' ORDER BY ' . $orderby : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('rating', $rating);
		if ($limit) $query->bindValue('limit', intval($limit), \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', intval($offset), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel($id, $title, $year, $rating, $poster, $allocine);
				array_push($this->collection, $movie);
			}
			return $this->collection;
		} else {
			return array();
		}
	}

	/*
	 * Mettre a jour un film
	 */
	public function update(object $content) {

		try {
			$query = $this->db->prepare('UPDATE ' . self::TABLE . ' SET title = :title, year = :year, rating = :rating, poster = :poster, allocine = :allocine WHERE id=:id;');
			$query->bindValue('id', $content->id);
			$query->bindValue('title', $content->title);
			if (!isset($content->year)) $content->year = null;
			if (!isset($content->rating)) $content->rating = null;
			if (!isset($content->poster)) $content->poster = null;
			if (!isset($content->allocine)) $content->allocine = null;
			$query->bindValue('year', $content->year);
			$query->bindValue('rating', $content->rating);
			$query->bindValue('poster', $content->poster);
			$query->bindValue('allocine', $content->allocine);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}

	/*
	 * Supprimer un film par id
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
	 * Dissocier un film d un realisateur
	 */
	public function deleteDirector($movie, $director) {

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

	/*
	 * Dissocier un film d une categorie
	 */
	public function deleteCategory($movie, $category) {

		try {
			$query = $this->db->prepare('DELETE FROM moviecategory WHERE movie=:movie AND category=:category;');
			$query->bindValue('movie', $movie);
			$query->bindValue('category', $category);
			$query->execute();

		} catch (PDOExecption $exception) {
			throw new \Exception($exception->getMessage());
			return false;
		}
		return intval($query->rowCount());
	}
}