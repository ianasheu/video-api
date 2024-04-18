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
	 * Effectuer une methode
	 */
	public function perform($method, array $url, array $filter=null, object $content=null, $connected=null) : array {

		$this->response_code = 200;
		$this->response_content = null;

		$orderby = null;
		$limit = null;
		$offset = null;
		$detailed = null;
		foreach ($filter as $f) {
			if (str_contains($f, '=')) {
				list($k, $v) = explode('=', $f);
				if ($v != '') {
					switch ($k){
						case 'orderby':
							$orderby = $v;
							break;
						case 'limit':
							$limit = $v;
							break;
						case 'offset':
							$offset = $v;
							break;
						case 'detailed':
							$detailed = $v;
							break;
						default:
							$this->response_code = 400;
							$this->response_content = 'wrong filter';
							return [$this->response_code, $this->response_content];
					}
				} else {
					$this->response_code = 400;
					$this->response_content = 'wrong filter';
					return [$this->response_code, $this->response_content];
				}
			} else {
				$this->response_code = 400;
				$this->response_content = 'wrong filter';
				return [$this->response_code, $this->response_content];
			}
		}

		if ($url[0] == self::ROUTE) {
			switch ($method) {
				case 'POST':
					if ($connected) {
						if (isset($url[1]) && $url[1]=='director' && !isset($url[2])) {
							if (!$filter) {
								if ((array)$content && isset($content->movie) && isset($content->director)) {
									$this->postDirector($content);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (isset($url[1]) && $url[1]=='category' && !isset($url[2])) {
							if (!$filter) {
								if ((array)$content && isset($content->movie) && isset($content->category)) {
									$this->postCategory($content);
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
								if ((array)$content && isset($content->title) &&
									(!isset($content->year) || isset($content->year) && is_numeric($content->year)) &&
									(!isset($content->rating) || isset($content->rating) && is_numeric($content->rating))) {
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
						if (isset($url[3]) && $url[3]=='director' && !isset($url[4])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->getDirector($url[2]);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (isset($url[3]) && $url[3]=='category' && !isset($url[4])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->getCategory($url[2]);
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
					if (isset($url[1]) && $url[1]=='title') {
						if (isset($url[2]) && $url[2]!='' && !isset($url[3])) {
							if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
								if (!(array)$content) {
									$this->getByTitle($url[2], $orderby, $limit, $offset);
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
					if (isset($url[1]) && $url[1]=='year') {
						if (isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
							if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
								if (!(array)$content) {
									$this->getByYear($url[2], $orderby, $limit, $offset);
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
					if (isset($url[1]) && $url[1]=='rating') {
						if (isset($url[2]) && $url[2]!='' && is_numeric($url[2]) && !isset($url[3])) {
							if ((!$orderby || $orderby && $this->model->existsProperty($orderby)) &&
								(!$limit || $limit && is_numeric($limit)) &&
								(!$offset || $limit && $offset && is_numeric($offset)) && !$detailed) {
								if (!(array)$content) {
									$this->getByRating($url[2], $orderby, $limit, $offset);
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
								if ((array)$content && isset($content->id) && is_numeric($content->id) && isset($content->title) &&
									(!isset($content->year) || isset($content->year) && is_numeric($content->year)) &&
									(!isset($content->rating) || isset($content->rating) && is_numeric($content->rating))) {
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
							isset($url[3]) && $url[3]=='director' && 
							isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->deleteDirector($url[2], $url[5]);
								} else {
									$this->response_code = 400;
									$this->response_content = 'wrong content';
								}
							} else {
								$this->response_code = 400;
								$this->response_content = 'wrong filter';
							}
						} else
						if (isset($url[1]) && $url[1]=='id' && isset($url[2]) && $url[2]!='' && is_numeric($url[2]) &&
							isset($url[3]) && $url[3]=='category' && 
							isset($url[4]) && $url[4]=='id' && isset($url[5]) && $url[5]!='' && is_numeric($url[5]) && !isset($url[6])) {
							if (!$filter) {
								if (!(array)$content) {
									$this->deleteCategory($url[2], $url[5]);
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
		return [$this->response_code, $this->response_content];
	}

	/*
	 * Envoyer un film
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
	 * Associer un film a un realisateur
	 */
	private function postDirector(object $content) {
		$response_content = $this->model->createDirector($content);

		if ($response_content !== false) {
			$this->response_code = 201;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Associer un film a une categorie
	 */
	private function postCategory(object $content) {
		$response_content = $this->model->createCategory($content);

		if ($response_content !== false) {
			$this->response_code = 201;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 503;
		}
	}

	/*
	 * Obtenir tous les films
	 */
	private function getAll($orderby=null, $limit=null, $offset=null) {
		$response_content = $this->model->readAll($orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir un film par l id
	 */
	private function getById($id, $detailed=null) {
		$response_content = $this->model->readById($id, $detailed);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir les realisateurs d un film
	 */
	private function getDirector($id) {
		$response_content = $this->model->readDirector($id);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir les categories d un film
	 */
	private function getCategory($id) {
		$response_content = $this->model->readCategory($id);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir des films par titre
	 */
	private function getByTitle($title, $orderby=null, $limit=null, $offset=null) {
		$title = str_replace('*', '%', $title);
		$response_content = $this->model->readByTitle($title, $orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir des films par annee
	 */
	private function getByYear($year, $orderby=null, $limit=null, $offset=null) {
		$response_content = $this->model->readByYear($year, $orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Obtenir des films par note
	 */
	private function getByRating($rating, $orderby=null, $limit=null, $offset=null) {
		$response_content = $this->model->readByRating($rating, $orderby, $limit, $offset);

		if (is_array($response_content) && !empty($response_content)) {
			$this->response_code = 200;
			$this->response_content = json_encode($response_content);
		} else {
			$this->response_code = 404;
			$this->response_content = json_encode(array());
		}
	}

	/*
	 * Mettre a jour un film
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
	 * Supprimer un film par id
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
	 * Dissocier un film d un realisateur
	 */
	private function deleteDirector($movie, $director) {
		$response_content = $this->model->deleteDirector($movie, $director);

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
	 * Dissocier un film d une categorie
	 */
	private function deleteCategory($movie, $category) {
		$response_content = $this->model->deleteCategory($movie, $category);

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