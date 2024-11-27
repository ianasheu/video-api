<?php
/*-----------------------------------------*
 *
 * Interface a implementer par les modeles
 *
 *-----------------------------------------*/

namespace api\Models;

interface CollectionModelInterface {
	/*
	 * Constructeur
	 */
	public function __construct();

	/*
	 * Evalue l existence d une propriete dans la classe item associee
	 *
	 * @param string $property
	 * @return boolean
	 */
	public static function existsProperty($property);

	/*
	 * Creer
	 *
	 * @param object $content
	 * @return int|boolean id cree ou false
	 */
	public function create(object $content);

	/*
	 * Lire tous
	 *
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function readAll($orderby=null, $limit=null, $offset=null) : array;

	/*
	 * Lire par l id
	 *
	 * @param int $id
	 * @return array
	 */
	public function readById($id) : array;

	/*
	 * Mettre a jour
	 * 
	 * @param object $content
	 * @return int|boolean nb de modif ou false
	 */
	public function update(object $content);

	/*
	 * Supprimer par l id
	 * 
	 * @param int $id
	 * @return int|boolean nb de supression ou false
	 */
	public function deleteById($id);
}