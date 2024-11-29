<?php
/*---------------------------*
 *
 * Modele pour une categorie
 *
 *---------------------------*/

declare(strict_types=1);

namespace api\Models;

class CategoryItemModel {

	/*
	 * @property int $id
	 * @property string $tag
	 */
	public int $id;
	public string $tag;

	/*
	 * Constructeur
	 *
	 * @param int $id
	 * @param string $tag
	 */
	public function __construct(int $id, string $tag) {
		$this->id = $id;
		$this->tag = $tag;
	}
}