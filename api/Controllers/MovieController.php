<?php
/*---------------------------*
 *
 * Controleur pour les films
 *
 *---------------------------*/

namespace api\Controllers;

use \api\Models\CollectionModelInterface;

class MovieController implements ControllerInterface {

	// Propriétés
	private const ROUTE = 'movie';
	private CollectionModelInterface $model;
	private $response_code;
	private $response_content;
	private $response_count;

	/*
	 * Constructeur
	 */
	public function __construct(CollectionModelInterface $model) {
		$this->model = $model;
	}

	/*
	 * Getter de route
	 */
	public function getRoute() {
		return self::ROUTE;
	}

	/*
	 * Effectuer une requete
	 */
	public function callModel($method, array $url, array $filter=null, object $content=null, $connected=null) : array {

		$orderby = null;
		$limit = null;
		$offset = null;
		$detailed = null;
		$filterAvailable = array('orderby', 'limit', 'offset', 'detailed');
		foreach ($filter as $fltr) {
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
					$limit = $value;
					break;
				case 'offset':
					$offset = $value;
					break;
				case 'detailed':
					$detailed = $value;
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
							$this->getDirector($url[2]);
						} else {
							return [400, 'wrong filter'];
						}
					} else
					if (isset($url[3]) && $url[3]=='category' && !isset($url[4])) {
						if (!$filter) {
							$this->getCategory($url[2]);
						} else {
							return [400, 'wrong filter'];
						}
					} else
					if (!isset($url[3])) {
						if ((!$detailed || $detailed=='false' || $detailed=='true')
							&& !$orderby && !$limit && !$offset) {
							$this->getById($url[2], $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (isset($url[1]) && $url[1]=='title') {
					if (isset($url[2]) && $url[2]!='' && !isset($url[3])) {
						if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
							(!$limit || $limit && is_numeric($limit)) &&
							(!$offset || $limit && $offset && is_numeric($offset)) &&
							(!$detailed || $detailed=='false' || $detailed=='true')) {
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
						if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
							(!$limit || $limit && is_numeric($limit)) &&
							(!$offset || $limit && $offset && is_numeric($offset)) &&
							(!$detailed || $detailed=='false' || $detailed=='true')) {
							$this->getByYear($url[2], $orderby, $limit, $offset, $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (isset($url[1]) && $url[1]=='rating') {
					if (isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
						if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
							(!$limit || $limit && is_numeric($limit)) &&
							(!$offset || $limit && $offset && is_numeric($offset)) &&
							(!$detailed || $detailed=='false' || $detailed=='true')) {
							$this->getByRating($url[2], $orderby, $limit, $offset, $detailed);
						} else {
							return [400, 'wrong filter'];
						}
					} else {
						return [400, 'wrong url'];
					}
				} else
				if (!isset($url[1])) {
					if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
						(!$limit || $limit && is_numeric($limit)) &&
						(!$offset || $limit && $offset && is_numeric($offset)) &&
						(!$detailed || $detailed=='false' || $detailed=='true')) {
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
					$this->deleteDirector($url[2], $url[5]);
				} else
				if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2]) &&
					isset($url[3]) && $url[3]=='category' &&
					isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
					$this->deleteCategory($url[2], $url[5]);
				} else
				if (isset($url[1]) && $url[1]=='id' &&
					isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
					$this->deleteById($url[2]);
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
	 */
	private function post(object $content) {
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
	 */
	private function postDirector(object $content) {
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
	 */
	private function postCategory(object $content) {
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
	 */
	private function getAll($orderby=null, $limit=null, $offset=null, $detailed=null) {
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
	 */
	private function getById($id, $detailed=null) {
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
	 */
	private function getDirector($id) {
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
	 */
	private function getCategory($id) {
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
	 */
	private function getByTitle($title, $orderby=null, $limit=null, $offset=null, $detailed=null) {
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
	 */
	private function getByYear($year, $orderby=null, $limit=null, $offset=null, $detailed=null) {
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
	 */
	private function getByRating($rating, $orderby=null, $limit=null, $offset=null, $detailed=null) {
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
	 */
	private function put(object $content) {
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
	 */
	private function deleteById($id) {
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
	 */
	private function deleteDirector($movie, $director) {
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
	 */
	private function deleteCategory($movie, $category) {
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