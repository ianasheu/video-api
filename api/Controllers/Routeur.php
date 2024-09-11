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
	private $registered;
	private $authenticated;
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
		if ($filter) {
			$filter = trim($filter, '&');
			$this->filter = explode('&', $filter);
		} else {
			$this->filter = array();
		}
		
		if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
			$this->content = (object)json_decode(file_get_contents('php://input'));
		} else {
			$this->content = new \stdClass();
		}
		
		if (isset($_SERVER['HTTP_X_API_KEY'])) {
			$this->registered = false;
			$this->authenticated = false;
			foreach (USERS as $user) {
				if ($user['api_key'] == $_SERVER['HTTP_X_API_KEY']) {
					$this->registered = true;
					if (isset($_SERVER['PHP_AUTH_USER']) && $user['login'] == $_SERVER['PHP_AUTH_USER'] &&
						isset($_SERVER['PHP_AUTH_PW']) && $user['password'] == $_SERVER['PHP_AUTH_PW'] &&
						$user['access'] == WRITE_ACCESS) {
						$this->authenticated = true;
					}
					break;
				}
			}
		} else {
			$this->registered = false;
			$this->authenticated = false;
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
	public function callController() : array {

		if (!is_array($this->url) || empty($this->url)) {
			return [400, 'wrong url'];
		}
		
		foreach ($this->controller as $ctrl) {
			if ($ctrl->getRoute() == $this->url[0]) {
				$controller = $ctrl;
				break;
			}
		}
		
		if (!isset($controller)) {
			return [404];
		}
		
		if (!$this->registered) {
			return [403];
		}
		
		return $controller->callModel($this->method, $this->url, $this->filter, $this->content, ($this->method=='GET' ? $this->registered : $this->authenticated));
	}
}