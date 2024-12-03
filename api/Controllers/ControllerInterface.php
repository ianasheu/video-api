<?php
/*---------------------------------------------*
 *
 * Interface a implementer par les controleurs
 *
 *---------------------------------------------*/

declare(strict_types=1);

namespace api\Controllers;

use api\Models\CollectionModelInterface;

interface ControllerInterface {
	/*
	 * Constructeur
	 *
	 * @param object $model
	 */
	public function __construct(CollectionModelInterface $model);

	/*
	 * Getter de route
	 *
	 * @return string
	 */
	public function getRoute() : string;

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
	public function callModel(string $method, array $url, ?array $filter=null, ?object $content=null, ?bool $connected=null) : array;
}