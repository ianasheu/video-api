<?php
/*---------------------------*
 *
 * Modele pour une categorie
 *
 *---------------------------*/

namespace api\Models;

class CategoryItemModel {

	/*
	 * @property int $id
	 * @property string $tag
	 */
	public $id;
	public $tag;

	/*
	 * Constructeur
	 *
	 * @param int $id
	 * @param string $tag
	 */
	public function __construct($id=null, $tag=null) {
		$this->id = $id;
		$this->tag = $tag;
	}
}