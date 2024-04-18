<?php
/*-----------------------------------------*
 * 
 * Interface a implementer par les modeles
 * 
 *-----------------------------------------*/

namespace api\Models;

interface CollectionModelInterface {
	public function __construct();
	public static function existsProperty($property);
	public function create(object $content);
	public function readAll($orderby=null, $limit=null, $offset=null) : array;
	public function readById($id) : array;
	public function update(object $content);
	public function deleteById($id);
}