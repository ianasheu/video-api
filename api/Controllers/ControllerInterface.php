<?php
/*---------------------------------------------*
 *
 * Interface a implementer par les controleurs
 *
 *---------------------------------------------*/

namespace api\Controllers;

use \api\Models\CollectionModelInterface;

interface ControllerInterface {
	public function __construct(CollectionModelInterface $model);
	public function getRoute();
	public function perform($method, array $url, array $filter=null, object $content=null, $connected=null) : array;
}