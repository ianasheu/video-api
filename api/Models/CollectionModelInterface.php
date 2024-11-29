<?php
/*-----------------------------------------*
 *
 * Interface a implementer par les modeles
 *
 *-----------------------------------------*/

declare(strict_types=1);

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
	 * @return bool
	 */
	public static function existsProperty(string $property) : bool;

	/*
	 * Creer
	 *
	 * @param object $content
	 * @return int|bool id cree ou false
	 */
	public function create(object $content) : int|bool;

	/*
	 * Lire tous
	 *
	 * @param string $orderby
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function readAll(?string $orderby=null, ?int $limit=null, ?int $offset=null) : array;

	/*
	 * Lire par l id
	 *
	 * @param int $id
	 * @return array
	 */
	public function readById(int $id) : array;

	/*
	 * Mettre a jour
	 * 
	 * @param object $content
	 * @return int|bool nb de modif ou false
	 */
	public function update(object $content) : int|bool;

	/*
	 * Supprimer par l id
	 * 
	 * @param int $id
	 * @return int|bool nb de supression ou false
	 */
	public function deleteById(int $id) : int|bool;
}