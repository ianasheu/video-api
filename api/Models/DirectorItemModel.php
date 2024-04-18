<?php
/*----------------------------*
 * 
 * Modele pour un realisateur
 * 
 *----------------------------*/

namespace api\Models;

class DirectorItemModel {

	// Propriétés
	public $id;
	public $name;
	public $country;

	/*
	 * Constructeur
	 */
	public function __construct($id=null, $name=null, $country=null) {
		$this->id = $id;
		$this->name = $name;
		$this->country = $country;
	}
}