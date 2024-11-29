<?php
/*----------------------------*
 *
 * Modele pour un realisateur
 *
 *----------------------------*/

declare(strict_types=1);

namespace api\Models;

class DirectorItemModel {

	/*
	 * @property int $id
	 * @property string $name
	 * @property string $country
	 */
	public int $id;
	public string $name;
	public ?string $country;

	/*
	 * Constructeur
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $country
	 */
	public function __construct(int $id, string $name, ?string $country=null) {
		$this->id = $id;
		$this->name = $name;
		$this->country = $country;
	}
}