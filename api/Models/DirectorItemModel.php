<?php
/*----------------------------*
 *
 * Modele pour un realisateur
 *
 *----------------------------*/

namespace api\Models;

class DirectorItemModel {

	/*
	 * @property int $id
	 * @property string $name
	 * @property string $country
	 */
	public $id;
	public $name;
	public $country;

	/*
	 * Constructeur
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $country
	 */
	public function __construct($id=null, $name=null, $country=null) {
		$this->id = $id;
		$this->name = $name;
		$this->country = $country;
	}
}