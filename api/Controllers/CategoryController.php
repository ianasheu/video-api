<?php
/*--------------------------------*
 *
 * Controleur pour les categories
 *
 *--------------------------------*/

namespace api\Controllers;

use \api\Models\CollectionModelInterface;

class CategoryController implements ControllerInterface {

	// Propriétés
	private const ROUTE = 'category';
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
		$filterAvailable = array('orderby', 'limit', 'offset');
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
				if (isset($url[1]) && $url[1]=='movie' && !isset($url[2])) {
					if ((array)$content && isset($content->movie) && isset($content->category)) {
						$this->postMovie($content);
					} else {
						return [400, 'wrong content'];
					}
				} else
				if (!isset($url[1])) {
					if ((array)$content && isset($content->tag)) {
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
					if (isset($url[3]) && $url[3]=='movie' && !isset($url[4])) {
						if ((!$orderby || $orderby && \api\Models\MovieCollectionModel::existsProperty($orderby)) &&
							(!$limit || $limit && is_numeric($limit)) &&
							(!$offset || $limit && $offset && is_numeric($offset))) {
							$this->getMovie($url[2], $orderby, $limit, $offset);
						} else {
							return [400, 'wrong filter'];
						}
					} else
					if (!isset($url[3])) {
						if (!$orderby && !$limit && !$offset) {
							$this->getById($url[2]);
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
						(!$offset || $limit && $offset && is_numeric($offset))) {
						$this->getAll($orderby, $limit, $offset);
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
					if ((array)$content && isset($content->id) && is_numeric($content->id) && isset($content->tag)) {
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
					isset($url[3]) && $url[3]=='movie' &&
					isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
					$this->deleteMovie($url[5], $url[2]);
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
	 * Envoyer une categorie
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
	 * Associer une categorie a un film
	 */
	private function postMovie(object $content) {
		$response_content = $this->model->createMovie($content);

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
	 * Obtenir tous les categories
	 */
	private function getAll($orderby=null, $limit=null, $offset=null) {
		list($response_content, $response_count) = $this->model->readAll($orderby, $limit, $offset);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir une categorie par l id
	 */
	private function getById($id) {
		list($response_content, $response_count) = $this->model->readById($id);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Obtenir les films d une categorie
	 */
	private function getMovie($id, $orderby=null, $limit=null, $offset=null) {
		list($response_content, $response_count) = $this->model->readMovie($id, $orderby, $limit, $offset);

		if (!is_array($response_content) || empty($response_content)) {
			$this->response_code = 404;
			return;
		}

		$this->response_code = 200;
		$this->response_content = json_encode($response_content);
		$this->response_count = $response_count;
	}

	/*
	 * Mettre a jour une categorie
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
	 * Supprimer une categorie par id
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
	 * Dissocier une categorie d un film
	 */
	private function deleteMovie($movie, $category) {
		$response_content = $this->model->deleteMovie($movie, $category);

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