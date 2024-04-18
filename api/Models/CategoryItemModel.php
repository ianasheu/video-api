<?php
/*---------------------------*
 * 
 * Modele pour une categorie
 * 
 *---------------------------*/

namespace api\Models;

class CategoryItemModel {

	// Propriétés
	public $id;
	public $tag;

	/*
	 * Constructeur
	 */
	public function __construct($id=null, $tag=null) {
		$this->id = $id;
		$this->tag = $tag;
	}
}