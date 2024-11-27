<?php
/*---------------------------------------------*
 *
 * Interface a implementer par les controleurs
 *
 *---------------------------------------------*/

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
	public function getRoute();

	/*
	 * Effectuer une requete sur le modele
	 *
	 * @param string $method
	 * @param array $url
	 * @param array $filter
	 * @param object $content
	 * @param boolean $connected
	 *
	 * @return array
	 */
	public function callModel($method, array $url, array $filter=null, object $content=null, $connected=null) : array;
}