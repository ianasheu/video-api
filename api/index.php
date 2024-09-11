<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/Autoloader.php';
\api\Autoloader::register();

use \api\Controllers\Routeur, 
	\api\Controllers\MovieController,
	\api\Controllers\DirectorController,
	\api\Controllers\CategoryController,
	\api\Models\MovieCollectionModel,
	\api\Models\DirectorCollectionModel,
	\api\Models\CategoryCollectionModel;

try {
	$root = new Routeur();

	$root->addController(new MovieController(new MovieCollectionModel()));
	$root->addController(new DirectorController(new DirectorCollectionModel()));
	$root->addController(new CategoryController(new CategoryCollectionModel()));

	$response = $root->callController();

	if (count($response) == 1) {
		list($response_code) = $response;
	} else
	if (count($response) == 2) {
		list($response_code, $response_content) = $response;
	} else
	if (count($response) == 3) {
		list($response_code, $response_content, $response_count) = $response;
	} else {
		throw new \Exception('Unexpected controller response');
	}

} catch (\Exception $exception) {

	if(!isset($response_code)) $response_code = 400;
	$response_content = $exception->getMessage();

}

require_once ROOT_PATH . '/Views/json_view.php';