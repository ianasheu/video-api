<?php
/*-----------------------*
 *
 * Modele pour les films
 *
 *-----------------------*/

declare(strict_types=1);

namespace api\Models;

use api\Models\CollectionModelInterface,
	api\Models\Database,
	api\Models\MovieItemModel,
	api\Models\DirectorItemModel,
	api\Models\CategoryItemModel;

class MovieCollectionModel implements CollectionModelInterface {

	/*
	 * @property string TABLE
	 * @property object $db
	 * @property array $collection
	 */
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
	 *
	 * @param string $property
	 * @return boolean
	 */
	public static function existsProperty(string $property) : bool {
		return (property_exists('api\Models\MovieItemModel', $property));
	}

	/*
	 * Creer un film
	 *
	 * @param object $content
	 * @return int|boolean id cree ou false
	 */
	public function create(object $content) : int|bool {

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
			return intval($this->db->lastInsertId());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Associer un film a un realisateur
	 *
	 * @param object $content
	 * @return int|boolean id cree ou false
	 * retourne zero si movie ou director ne sont pas des ids existants
	 */
	public function createDirector(object $content) : int|bool {

		try {
			$query = $this->db->prepare('INSERT INTO moviedirector (movie, director) VALUES (:movie, :director);');
			$query->bindValue('movie', intval($content->movie), \PDO::PARAM_INT);
			$query->bindValue('director', intval($content->director), \PDO::PARAM_INT);
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
	 * Associer un film a une categorie
	 *
	 * @param object $content
	 * @return int|boolean id cree ou false
	 * retourne zero si movie ou category ne sont pas des ids existants
	 */
	public function createCategory(object $content) : int|bool {

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
	 * Lire tous les films
	 *
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $detailed
	 * @return array
	 */
	public function readAll(?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : array {

		if (!$detailed) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS id as movieid, title, year, rating, poster, allocine FROM ' . self::TABLE;
		} else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id as movieid, movie.title, movie.year, movie.rating, movie.poster, movie.allocine, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", director.id, "name", director.name, "country", director.country )), "]") '.
				'FROM director, moviedirector WHERE director.id = moviedirector.director AND moviedirector.movie = movie.id AND movie.id = movieid ORDER BY director.name ASC) AS directors, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", category.id, "tag", category.tag )), "]") '.
				'FROM category, moviecategory WHERE category.id = moviecategory.category AND moviecategory.movie = movie.id AND movie.id = movieid ORDER BY moviecategory.id ASC) AS categories '.
				'FROM movie';
		}
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if ($limit) $query->bindValue('limit', $limit, \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', $offset, \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel(intval($movieid), $title, intval($year), floatval($rating), $poster, $allocine);
				if ($detailed) {
					$directors = json_decode($directors);
					$movie->director = array();
					foreach ($directors as $d) {
						array_push($movie->director, new DirectorItemModel(intval($d->id), $d->name, $d->country));
					}
					$categories = json_decode($categories);
					$movie->category = array();
					foreach ($categories as $c) {
						array_push($movie->category, new CategoryItemModel(intval($c->id), $c->tag));
					}
				}
				array_push($this->collection, $movie);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire un film par l id
	 *
	 * @param int $id
	 * @param boolean $detailed
	 * @return array
	 */
	public function readById(int $id, ?bool $detailed=null) : array {

		if (!$detailed) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS id as movieid, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE id = :id;';
		} else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id as movieid, movie.title, movie.year, movie.rating, movie.poster, movie.allocine, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", director.id, "name", director.name, "country", director.country )), "]") '.
				'FROM director, moviedirector WHERE director.id = moviedirector.director AND moviedirector.movie = movie.id AND movie.id = movieid ORDER BY director.name ASC) AS directors, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", category.id, "tag", category.tag )), "]") '.
				'FROM category, moviecategory WHERE category.id = moviecategory.category AND moviecategory.movie = movie.id AND movie.id = movieid ORDER BY moviecategory.id ASC) AS categories '.
				'FROM movie WHERE movie.id = :id';
		}
		$query = $this->db->prepare($sql);
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			$row = $query->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$movie = new MovieItemModel(intval($movieid), $title, intval($year), floatval($rating), $poster, $allocine);
			if ($detailed) {
				$directors = json_decode($directors);
				$movie->director = array();
				foreach ($directors as $d) {
					array_push($movie->director, new DirectorItemModel(intval($d->id), $d->name, $d->country));
				}
				$categories = json_decode($categories);
				$movie->category = array();
				foreach ($categories as $c) {
					array_push($movie->category, new CategoryItemModel(intval($c->id), $c->tag));
				}
			}
			array_push($this->collection, $movie);
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire les realisateurs d un film
	 *
	 * @param int $id
	 * @return array
	 */
	public function readDirector(int $id) : array {

		$result = array();
		$sql = 'SELECT SQL_CALC_FOUND_ROWS director.id, director.name, director.country FROM director, moviedirector WHERE moviedirector.director = director.id AND moviedirector.movie = :id ORDER BY director.name ASC;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$dir = new DirectorItemModel(intval($id), $name, $country);
				array_push($result, $dir);
			}
			return [$result, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire les categories d un film
	 *
	 * @param int $id
	 * @return array
	 */
	public function readCategory(int $id) : array {

		$result = array();
		$sql = 'SELECT SQL_CALC_FOUND_ROWS category.id, category.tag FROM category, moviecategory WHERE moviecategory.category = category.id AND moviecategory.movie = :id ORDER BY moviecategory.id ASC;';
		$query = $this->db->prepare($sql);
		$query->bindValue('id', intval($id), \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$cat = new CategoryItemModel(intval($id), $tag);
				array_push($result, $cat);
			}
			return [$result, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire des films par titre
	 *
	 * @param string $title
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $detailed
	 * @return array
	 */
	public function readByTitle(string $title, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : array {

		if (!$detailed) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS id as movieid, title, year, rating, poster, allocine FROM ' . self::TABLE;
		} else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id as movieid, movie.title, movie.year, movie.rating, movie.poster, movie.allocine, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", director.id, "name", director.name, "country", director.country )), "]") '.
				'FROM director, moviedirector WHERE director.id = moviedirector.director AND moviedirector.movie = movie.id AND movie.id = movieid ORDER BY director.name ASC) AS directors, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", category.id, "tag", category.tag )), "]") '.
				'FROM category, moviecategory WHERE category.id = moviecategory.category AND moviecategory.movie = movie.id AND movie.id = movieid ORDER BY moviecategory.id ASC) AS categories '.
				'FROM movie';
		}
		$words = trim($title, '%');
		$words = explode('%', $words);
		if (count($words) == 1) {
			$sql .= ' WHERE movie.title LIKE :title';
		} else
		if (count($words) > 1) {
			$sql .= ' WHERE movie.title LIKE :words0';
			for ($i=1; $i<count($words); $i++) {
				$sql .= " OR movie.title LIKE :words{$i}";
			}
		}
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		if (count($words) == 1) {
			$query->bindValue('title', $title);
		} else
		if (count($words) > 1) {
			for ($i=0; $i<count($words); $i++) {
				$query->bindValue("words{$i}", "%{$words[$i]}%");
			}
		}
		if ($limit) $query->bindValue('limit', $limit, \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', $offset, \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel(intval($movieid), $title, intval($year), floatval($rating), $poster, $allocine);
				if ($detailed) {
					$directors = json_decode($directors);
					$movie->director = array();
					foreach ($directors as $d) {
						array_push($movie->director, new DirectorItemModel(intval($d->id), $d->name, $d->country));
					}
					$categories = json_decode($categories);
					$movie->category = array();
					foreach ($categories as $c) {
						array_push($movie->category, new CategoryItemModel(intval($c->id), $c->tag));
					}
				}
				array_push($this->collection, $movie);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire des films par annee
	 *
	 * @param int $year
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $detailed
	 * @return array
	 */
	public function readByYear(int $year, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : array {

		if (!$detailed) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS id as movieid, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE year LIKE :year';
		} else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id as movieid, movie.title, movie.year, movie.rating, movie.poster, movie.allocine, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", director.id, "name", director.name, "country", director.country )), "]") '.
				'FROM director, moviedirector WHERE director.id = moviedirector.director AND moviedirector.movie = movie.id AND movie.id = movieid ORDER BY director.name ASC) AS directors, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", category.id, "tag", category.tag )), "]") '.
				'FROM category, moviecategory WHERE category.id = moviecategory.category AND moviecategory.movie = movie.id AND movie.id = movieid ORDER BY moviecategory.id ASC) AS categories '.
				'FROM movie WHERE movie.year LIKE :year';
		}
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('year', $year);
		if ($limit) $query->bindValue('limit', $limit, \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', $offset, \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel(intval($movieid), $title, intval($year), floatval($rating), $poster, $allocine);
				if ($detailed) {
					$directors = json_decode($directors);
					$movie->director = array();
					foreach ($directors as $d) {
						array_push($movie->director, new DirectorItemModel(intval($d->id), $d->name, $d->country));
					}
					$categories = json_decode($categories);
					$movie->category = array();
					foreach ($categories as $c) {
						array_push($movie->category, new CategoryItemModel(intval($c->id), $c->tag));
					}
				}
				array_push($this->collection, $movie);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Lire des films par note
	 *
	 * @param float $rating
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param boolean $detailed
	 * @return array
	 */
	public function readByRating(float $rating, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : array {

		if (!$detailed) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS id as movieid, title, year, rating, poster, allocine FROM ' . self::TABLE . ' WHERE rating LIKE :rating';
		} else {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS movie.id as movieid, movie.title, movie.year, movie.rating, movie.poster, movie.allocine, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", director.id, "name", director.name, "country", director.country )), "]") '.
				'FROM director, moviedirector WHERE director.id = moviedirector.director AND moviedirector.movie = movie.id AND movie.id = movieid ORDER BY director.name ASC) AS directors, '.
				'(SELECT CONCAT("[", GROUP_CONCAT(JSON_OBJECT( "id", category.id, "tag", category.tag )), "]") '.
				'FROM category, moviecategory WHERE category.id = moviecategory.category AND moviecategory.movie = movie.id AND movie.id = movieid ORDER BY moviecategory.id ASC) AS categories '.
				'FROM movie WHERE movie.rating = :rating';
		}
		$sql = ($orderby ? $sql . " ORDER BY {$orderby} ASC" : $sql);
		$sql = ($limit ? $sql . ' LIMIT :limit' : $sql);
		$sql = ($offset ? $sql . ' OFFSET :offset' : $sql);
		$query = $this->db->prepare($sql . ';');
		$query->bindValue('rating', $rating);
		if ($limit) $query->bindValue('limit', $limit, \PDO::PARAM_INT);
		if ($offset) $query->bindValue('offset', $offset, \PDO::PARAM_INT);
		$query->execute();

		if ($query->rowCount() > 0) {
			while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
				extract($row);
				$movie = new MovieItemModel(intval($movieid), $title, intval($year), floatval($rating), $poster, $allocine);
				if ($detailed) {
					$directors = json_decode($directors);
					$movie->director = array();
					foreach ($directors as $d) {
						array_push($movie->director, new DirectorItemModel(intval($d->id), $d->name, $d->country));
					}
					$categories = json_decode($categories);
					$movie->category = array();
					foreach ($categories as $c) {
						array_push($movie->category, new CategoryItemModel(intval($c->id), $c->tag));
					}
				}
				array_push($this->collection, $movie);
			}
			return [$this->collection, Database::getRowsCount()];
		} else {
			return array(null,0);
		}
	}

	/*
	 * Mettre a jour un film
	 * 
	 * @param object $content
	 * @return int|boolean nb de modif ou false
	 */
	public function update(object $content) : int|bool {

		try {
			$query = $this->db->prepare('UPDATE ' . self::TABLE . ' SET title = :title, year = :year, rating = :rating, poster = :poster, allocine = :allocine WHERE id=:id;');
			$query->bindValue('id', intval($content->id), \PDO::PARAM_INT);
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
			return intval($query->rowCount());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Supprimer un film par l id
	 * 
	 * @param int $id
	 * @return int|boolean nb de supression ou false
	 */
	public function deleteById(int $id) : int|bool {

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
	 * Dissocier un film d un realisateur
	 * 
	 * @param int $movie
	 * @param int $director
	 * @return int|boolean nb de supression ou false
	 */
	public function deleteDirector(int $movie, int $director) : int|bool {

		try {
			$query = $this->db->prepare('DELETE FROM moviedirector WHERE movie=:movie AND director=:director;');
			$query->bindValue('movie', intval($movie), \PDO::PARAM_INT);
			$query->bindValue('director', intval($director), \PDO::PARAM_INT);
			$query->execute();
			return intval($query->rowCount());

		} catch (\PDOException $exception) {
			return false;
		}
	}

	/*
	 * Dissocier un film d une categorie
	 * 
	 * @param int $movie
	 * @param int $category
	 * @return int|boolean nb de supression ou false
	 */
	public function deleteCategory(int $movie, int $category) : int|bool {

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