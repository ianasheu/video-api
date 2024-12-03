<?php
/*---------------------------*
 *
 * Controleur pour les films
 *
 *---------------------------*/

declare(strict_types=1);

namespace api\Controllers;

use api\Controllers\ControllerInterface,
	api\Models\CollectionModelInterface,
	api\Models\MovieCollectionModel;

class MovieController implements ControllerInterface {

	/*
	 * @property string ROUTE
	 * @property object $model
	 * @property int $response_code
	 * @property string $response_content
	 * @property int $response_count
	 */
	private const ROUTE = 'movie';
	private CollectionModelInterface $model;
	private $response_code;
	private $response_content;
	private $response_count;

	/*
	 * Constructeur
	 *
	 * @param object $model
	 */
	public function __construct(CollectionModelInterface $model) {
		$this->model = $model;
	}

	/*
	 * Getter de route
	 *
	 * @return string
	 */
	public function getRoute() : string {
		return self::ROUTE;
	}

	/*
	 * Effectuer une requete sur le modele
	 *
	 * @param string $method
	 * @param array $url
	 * @param array $filter
	 * @param object $content
	 * @param bool $connected
	 * @return array
	 */
	public function callModel(string $method, array $url, ?array $filter=null, ?object $content=null, ?bool $connected=null) : array {

		$orderby = null;
		$limit = null;
		$offset = null;
		$detailed = null;
		$filterAvailable = array('orderby', 'limit', 'offset', 'detailed');
		foreach ((array)$filter as $fltr) {
			if (!str_contains($fltr, '=')) {
				return [400, 'wrong filter'];
			}
			
			list($key, $value) = explode('=', $fltr);
			
			if (!in_array($key, $filterAvailable)) {
				return [400, 'wrong filter'];
			}
			if ($value == '') {
				return [400, 'wrong filter'];
			}
			
			unset($filterAvailable[array_search($key, $filterAvailable)]);
			
			switch ($key){
				case 'orderby':
					$orderby = $value;
					break;
				case 'limit':
					if (!is_numeric($value)) {
						return [400, 'wrong filter'];
					}
					$limit = intval($value);
					break;
				case 'offset':
					if (!is_numeric($value)) {
						return [400, 'wrong filter'];
					}
					$offset = intval($value);
					break;
				case 'detailed':
					if ($value!='true' && $value!='false') {
						return [400, 'wrong filter'];
					}
					$detailed = ($value=='true'?true:false);
					break;
			}
		}

		if ($url[0] != self::ROUTE) {
			return [400, 'wrong url'];
		}

		if (!in_array($method, array('POST', 'GET', 'PUT', 'DELETE'))) {
			return [405];
		}

		if (!$connected) {
			if($method != 'GET') {
				return [401];
			} else {
				return [403];
			}
		}

		switch ($method) {
			case 'POST':
				if ($filter) {
					return [400, 'wrong filter'];
				}
				if (isset($url[1]) && $url[1]=='director' && !isset($url[2])) {
					if ((array)$content && isset($content->movie) && isset($content->director)) {
						$this->postDirector($content);
					} else {
						return [400, 'wrong content'];
					}
				} else
				if (isset($url[1]) && $url[1]=='category' && !isset($url[2])) {
					if ((array)$content && isset($content->movie) && isset($content->category)) {
						$this->postCategory($content);
					} else {
						return [400, 'wrong content'];
					}
				} else
				if (!isset($url[1])) {
					if ((array)$content && isset($content->title) &&
						(!isset($content->year) || isset($content->year) && is_numeric($content->year)) &&
						(!isset($content->rating) || isset($content->rating) && is_numeric($content->rating))) {
						$this->post($content);
					} else {
						return [400, 'wrong content'];
					}
				} else {
					return [400, 'wrong url'];
				}
				break;
			case 'GET':
				if ((array)$content) {
					return [400, 'wrong content'];
				}
				if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2])) {
					if (isset($url[3]) && $url[3]=='director' && !isset($url[4])) {
						if (!$filter) {
							$this->getDirector(intval($url[2]));
						} else {
							return [400, 'wrong filter'];
						}
					} else
					if (isset($url[3]) && $url[3]=='category' && !isset($url[4])) {
						if (!$filter) {
							$this->getCategory(intval($url[2]));
						} else {
							return [400, 'wrong filter'];
						}
					} else
					if (!isset($url[3])) {
						if (!$orderby && !$limit && !$offset) {
							$this->getById(intval($url[2]), $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (isset($url[1]) && $url[1]=='title') {
					if (isset($url[2]) && $url[2]!='' && !isset($url[3])) {
						if ((!$orderby || $orderby && MovieCollectionModel::existsProperty($orderby)) &&
							(!$offset || $limit && $offset)) {
							$this->getByTitle($url[2], $orderby, $limit, $offset, $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						$this->response_code = 400;
						$this->response_content = 'wrong url';
					}
				} else
				if (isset($url[1]) && $url[1]=='year') {
					if (isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
						if ((!$orderby || $orderby && MovieCollectionModel::existsProperty($orderby)) &&
							(!$offset || $limit && $offset)) {
							$this->getByYear(intval($url[2]), $orderby, $limit, $offset, $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (isset($url[1]) && $url[1]=='rating') {
					if (isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
						if ((!$orderby || $orderby && MovieCollectionModel::existsProperty($orderby)) &&
							(!$offset || $limit && $offset)) {
							$this->getByRating(floatval($url[2]), $orderby, $limit, $offset, $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (!isset($url[1])) {
					if ((!$orderby || $orderby && MovieCollectionModel::existsProperty($orderby)) &&
						(!$offset || $limit && $offset)) {
						$this->getAll($orderby, $limit, $offset, $detailed);
					} else {
						return [400, 'wrong filter'];
					}
				} else {
					return [400, 'wrong url'];
				}
				break;
			case 'PUT':
				if ($filter) {
					return [400, 'wrong filter'];
				}
				if (!isset($url[1])) {
					if ((array)$content && isset($content->id) && is_numeric($content->id) && isset($content->title) &&
						(!isset($content->year) || isset($content->year) && is_numeric($content->year)) &&
						(!isset($content->rating) || isset($content->rating) && is_numeric($content->rating))) {
						$this->put($content);
					} else {
						return [400, 'wrong content'];
					}
				} else {
					return [400, 'wrong url'];
				}
				break;
			case 'DELETE':
				if ($filter) {
					return [400, 'wrong filter'];
				}
				if ((array)$content) {
					return [400, 'wrong content'];
				}
				if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2]) &&
					isset($url[3]) && $url[3]=='director' &&
					isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
					$this->deleteDirector(intval($url[2]), intval($url[5]));
				} else
				if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2]) &&
					isset($url[3]) && $url[3]=='category' &&
					isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
					$this->deleteCategory(intval($url[2]), intval($url[5]));
				} else
				if (isset($url[1]) && $url[1]=='id' &&
					isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
					$this->deleteById(intval($url[2]));
				} else {
					return [400, 'wrong url'];
				}
				break;
		}

		if (isset($this->response_count)) {
			return [$this->response_code, $this->response_content, $this->response_count];
		} else
		if (isset($this->response_content)) {
			return [$this->response_code, $this->response_content];
		}
		return [$this->response_code];
	}

	/*
	 * Envoyer un film
	 *
	 * @param object $content
	 */
	private function post(object $content) : void {
		$response_content = $this->model->create($content);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		$this->response_code = 201;
		$this->response_content = json_encode($response_content);
	}

	/*
	 * Associer un film a un realisateur
	 *
	 * @param object $content
	 */
	private function postDirector(object $content) : void {
		$response_content = $this->model->createDirector($content);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 400;
			$this->response_content = 'invalid parameter value';
			return;
		}

		$this->response_code = 201;
		$this->response_content = json_encode($response_content);
	}

	/*
	 * Associer un film a une categorie
	 *
	 * @param object $content
	 */
	private function postCategory(object $content) : void {
		$response_content = $this->model->createCategory($content);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 400;
			$this->response_content = 'invalid parameter value';
			return;
		}

		$this->response_code = 201;
		$this->response_content = json_encode($response_content);
	}

	/*
	 * Obtenir tous les films
	 *
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param bool $detailed
	 */
	private function getAll(?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : void {
		list($response_content, $response_count) = $this->model->readAll($orderby, $limit, $offset, $detailed);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir un film par l id
	 *
	 * @param int $id
	 * @param bool $detailed
	 */
	private function getById(int $id, ?bool $detailed=null) : void {
		list($response_content, $response_count) = $this->model->readById($id, $detailed);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir les realisateurs d un film
	 *
	 * @param int $id
	 */
	private function getDirector(int $id) : void {
		list($response_content, $response_count) = $this->model->readDirector($id);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir les categories d un film
	 *
	 * @param int $id
	 */
	private function getCategory(int $id) : void {
		list($response_content, $response_count) = $this->model->readCategory($id);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir des films par titre
	 *
	 * @param string $title
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param bool $detailed
	 */
	private function getByTitle(string $title, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : void {
		$title = str_replace('*', '%', $title);
		list($response_content, $response_count) = $this->model->readByTitle($title, $orderby, $limit, $offset, $detailed);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir des films par annee
	 *
	 * @param int $year
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param bool $detailed
	 */
	private function getByYear(int $year, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : void {
		list($response_content, $response_count) = $this->model->readByYear($year, $orderby, $limit, $offset, $detailed);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir des films par note
	 *
	 * @param float $rating
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @param bool $detailed
	 */
	private function getByRating(float $rating, ?string $orderby=null, ?int $limit=null, ?int $offset=null, ?bool $detailed=null) : void {
		list($response_content, $response_count) = $this->model->readByRating($rating, $orderby, $limit, $offset, $detailed);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Mettre a jour un film
	 * 
	 * @param object $content
	 */
	private function put(object $content) : void {
		$response_content = $this->model->update($content);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 204;
	}

	/*
	 * Supprimer un film par id
	 * 
	 * @param int $id
	 */
	private function deleteById(int $id) : void {
		$response_content = $this->model->deleteById($id);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 204;
	}

	/*
	 * Dissocier un film d un realisateur
	 * 
	 * @param int $movie
	 * @param int $director
	 */
	private function deleteDirector(int $movie, int $director) : void {
		$response_content = $this->model->deleteDirector($movie, $director);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 204;
	}

	/*
	 * Dissocier un film d une categorie
	 * 
	 * @param int $movie
	 * @param int $category
	 */
	private function deleteCategory(int $movie, int $category) : void {
		$response_content = $this->model->deleteCategory($movie, $category);

		if ($response_content === false) {
			$this->response_code = 503;
			return;
		}

		if ($response_content == 0) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 204;
	}
}