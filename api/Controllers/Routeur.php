<?php
/*------------------------------------*
 *
 * Routeur vers le controleur adequat
 *
 *------------------------------------*/

namespace api\Controllers;

use \api\Controllers\ControllerInterface;

require_once ROOT_PATH . '/Controllers/users_config.php';

class Routeur {
	// Propriétés
	private const API_PATH = '/video/api/';
	private $method;
	private array $url;
	private array $filter;
	private object $content;
	private $connected;
	private array $controller;

	/*
	 * Constructeur
	 */
	public function __construct() {
		
		$this->method = $_SERVER['REQUEST_METHOD'];
		
		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		if (str_contains($url, '&') && !str_contains($url, '?')) {
			$this->url = array();
		} else {
			$url = trim(str_replace(self::API_PATH, '', $url), '/');
			if ($url) {
				$this->url = explode('/', $url);
			} else {
				$this->url = array();
			}
		}
		
		$filter = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		$filter = trim($filter, '&');
		if ($filter) {
			$this->filter = explode('&', $filter);
		} else {
			$this->filter = array();
		}
		
		if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
			$this->content = (object)json_decode(file_get_contents('php://input'));
		} else {
			$this->content = new \stdClass();
		}
		
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$this->connected = (!array_key_exists($_SERVER['PHP_AUTH_USER'], USERS) ? false :
				(USERS[$_SERVER['PHP_AUTH_USER']]!=$_SERVER['PHP_AUTH_PW'] ? false : true)
			);
		} else {
			$this->connected = false;
		}
		
		$this->controller = array();
	}
	
	/*
	 * Ajouter un controller
	 */
	public function addController(ControllerInterface $controller) : void {
		array_push($this->controller, $controller);
	}

	/*
	 * Effectuer une methode
	 */
	public function perform() : array {
		$response_code = 200;
		$response_content = null;
		$controller = null;
		if (is_array($this->url) && !empty($this->url)) {
			foreach ($this->controller as $ctrl) {
				if ($ctrl->getRoute() == $this->url[0]) {
					$controller = $ctrl;
					break;
				}
			}
			if ($controller) {
				$response = $controller->perform($this->method, $this->url, $this->filter, $this->content, $this->connected);
				return $response;
			} else {
				$response_code = 404;
				$response_content = 'ressource not found';
			}
		} else {
			$response_code = 403;
		}
		return [$response_code, $response_content];
	}
}