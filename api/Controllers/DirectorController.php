<?php
/*----------------------------------*
 *
 * Controleur pour les realisateurs
 *
 *----------------------------------*/

namespace api\Controllers;

use \api\Models\CollectionModelInterface;

class DirectorController implements ControllerInterface {

	// Propriétés
	private const ROUTE = 'director';
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
		$this->response_count = null;

		$orderby = null;
		$limit = null;
		$offset = null;
		$detailed = null;
		$filterAvailable = array('orderby', 'limit', 'offset', 'detailed');
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
							case 'detailed':
								$detailed = $value;
								break;
							default:
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
								if ((array)$content && isset($content->movie) && isset($content->director)) {
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
								if ((array)$content && isset($content->name)) {
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
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
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
							if ((!$detailed || $detailed=='false' || $detailed=='true')
								&& !$orderby && !$limit && !$offset) {
								if (!(array)$content) {
									$this->getById($url[2], $detailed);
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
					if (isset($url[1]) && $url[1]=='name') {
						if (isset($url[2]) && $url[2]!='' && !isset($url[3])) {
							if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
								if (!(array)$content) {
									$this->getByName($url[2], $orderby, $limit, $offset);
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
					if (isset($url[1]) && $url[1]=='country') {
						if (isset($url[2]) && $url[2]!='' && !isset($url[3])) {
							if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
								if (!(array)$content) {
									$this->getByCountry($url[2], $orderby, $limit, $offset);
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
							(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
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
								if ((array)$content && isset($content->id) && is_numeric($content->id) && isset($content->name)) {
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
	 * Envoyer un realisateur
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
	 * Associer un realisateur a un film
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
	 * Obtenir tous les realisateurs
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
	 * Obtenir un realisateur par l id
	 */
	private function getById($id, $detailed=null) {
		list($response_content, $response_count) = $this->model->readById($id, $detailed);

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
	 * Obtenir les films d un realisateur
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
	 * Obtenir des realisateurs par nom
	 */
	private function getByName($name, $orderby=null, $limit=null, $offset=null) {
		$name = str_replace('*', '%', $name);
		list($response_content, $response_count) = $this->model->readByName($name, $orderby, $limit, $offset);

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
	 * Obtenir des realisateurs par pays
	 */
	private function getByCountry($country, $orderby=null, $limit=null, $offset=null) {
		list($response_content, $response_count) = $this->model->readByCountry($country, $orderby, $limit, $offset);

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
	 * Mettre a jour un realisateur
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
	 * Supprimer un realisateur par id
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
	 * Dissocier un realisateur d un film
	 */
	private function deleteMovie($movie, $director) {
		$response_content = $this->model->deleteMovie($movie, $director);

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