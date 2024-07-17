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
	public function perform($method, array $url, array $filter=null, object $content=null, $connected=null) : array {

		$this->response_code = 200;
		$this->response_content = null;
		$this->response_count = 0;

		$orderby = null;
		$limit = null;
		$offset = null;
		$filterAvailable = array("orderby", "limit", "offset");
		foreach ($filter as $f) {
			if (str_contains($f, '=')) {
				list($key, $value) = explode('=', $f);
				if ($value != '') {
					if (in_array($key, $filterAvailable)) {
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
							default:
								$response_code = 400;
								$this->response_content = 'wrong filter';
								return [$this->response_code, $this->response_content, $this->response_count];
						}
					} else {
						$this->response_code = 400;
						$this->response_content = 'wrong filter';
						return [$this->response_code, $this->response_content, $this->response_count];
					}
				} else {
					$this->response_code = 400;
					$this->response_content = 'wrong filter';
					return [$this->response_code, $this->response_content, $this->response_count];
				}
			} else {
				$this->response_code = 400;
				$this->response_content = 'wrong filter';
				return [$this->response_code, $this->response_content, $this->response_count];
			}
		}

		if ($url[0] == self::ROUTE) {
			switch ($method) {
				case 'POST':
					if ($connected) {
						if (isset($url[1]) && $url[1]=='movie' && !isset($url[2])) {
							if (!$filter) {
								if ((array)$content && isset($content->movie) && isset($content->category)) {
									$this->postMovie($content);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (!isset($url[1])) {
							if (!$filter) {
								if ((array)$content && isset($content->tag)) {
									$this->post($content);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else {
							$this->response_code = 400;
							$this->response_content = 'wrong url';
						}
					} else {
						$this->response_code = 401;
					}
					break;
				case 'GET':
					if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2])) {
						if (isset($url[3]) && $url[3]=='movie' && !isset($url[4])) {
							if ((!$orderby || $orderby && \api\Models\MovieCollectionModel::existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset))) {
								if (!(array)$content) {
									$this->getMovie($url[2], $orderby, $limit, $offset);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (!isset($url[3])) {
							if (!$orderby && !$limit && !$offset) {
								if (!(array)$content) {
									$this->getById($url[2]);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else {
							$this->response_code = 400;
							$this->response_content = 'wrong url';
						}
					} else
					if (!isset($url[1])) {
						if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
							(!$limit || $limit && is_numeric($limit)) &&
							(!$offset || $limit && $offset && is_numeric($offset))) {
							if (!(array)$content) {
								$this->getAll($orderby, $limit, $offset);
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong content';
							}
						} else {
							$this->response_code = 400;
							$this->response_content = 'wrong filter';
						}
					} else {
						$this->response_code = 400;
						$this->response_content = 'wrong url';
					}
					break;
				case 'PUT':
					if ($connected) {
						if (!isset($url[1])) {
							if (!$filter) {
								if ((array)$content && isset($content->id) && is_numeric($content->id) && isset($content->tag)) {
									$this->put($content);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else {
							$this->response_code = 400;
							$this->response_content = 'wrong url';
						}
					} else {
						$this->response_code = 401;
					}
					break;
				case 'DELETE':
					if ($connected) {
						if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2]) &&
							isset($url[3]) && $url[3]=='movie' &&
							isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->deleteMovie($url[5], $url[2]);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (isset($url[1]) && $url[1]=='id' &&
							isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->deleteById($url[2]);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else {
							$this->response_code = 400;
							$this->response_content = 'wrong url';
						}
					} else {
						$this->response_code = 401;
					}
					break;
				default :
					$this->response_code = 405;
			}
		} else {
			$this->response_code = 400;
			$this->response_content = 'wrong url';
		}
		return [$this->response_code, $this->response_content, $this->response_count];
	}

	/*
	 * Envoyer une categorie
	 */
	private function post(object $content) {
		$response_content = $this->model->create($content);

		if ($response_content !== false) {
			$this->response_code = 201;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Associer une categorie a un film
	 */
	private function postMovie(object $content) {
		$response_content = $this->model->createMovie($content);

		if ($response_content !== false) {
			$this->response_code = 201;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Obtenir tous les categories
	 */
	private function getAll($orderby=null, $limit=null, $offset=null) {
		list($response_content, $response_count) = $this->model->readAll($orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
			$this->response_count = $response_count;
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir une categorie par l id
	 */
	private function getById($id) {
		list($response_content, $response_count) = $this->model->readById($id);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
			$this->response_count = $response_count;
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir les films d une categorie
	 */
	private function getMovie($id, $orderby=null, $limit=null, $offset=null) {
		list($response_content, $response_count) = $this->model->readMovie($id, $orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
			$this->response_count = $response_count;
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Mettre a jour une categorie
	 */
	private function put(object $content) {
		$response_content = $this->model->update($content);

		if ($response_content !== false) {
			$this->response_code = 202;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Supprimer une categorie par id
	 */
	private function deleteById($id) {
		$response_content = $this->model->deleteById($id);

		if ($response_content !== false) {
			if ($response_content != 0) {
				$this->response_code = 200;
				$this->response_content = json_encode($response_content);
			} else {
				$this->response_code = 404;
				$this->response_content = json_encode($response_content);
			}
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Dissocier une categorie d un film
	 */
	private function deleteMovie($movie, $category) {
		$response_content = $this->model->deleteMovie($movie, $category);

		if ($response_content !== false) {
			if ($response_content != 0) {
				$this->response_code = 200;
				$this->response_content = json_encode($response_content);
			} else {
				$this->response_code = 404;
				$this->response_content = json_encode($response_content);
			}
		} else {
			$this->response_code = 503;
		}
	}
}