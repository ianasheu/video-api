<?php
/*---------------------*
 *
 * Modele pour un film
 *
 *---------------------*/

namespace api\Models;

class MovieItemModel {

	/*
	 * @property int $id
	 * @property string $title
	 * @property int $year
	 * @property float $rating
	 * @property string $poster
	 * @property string $allocine
	 */
	public $id;
	public $title;
	public $year;
	public $rating;
	public $poster;
	public $allocine;

	/*
	 * Constructeur
	 *
	 * @param int $id
	 * @param string $title
	 * @param int $year
	 * @param float $rating
	 * @param string $poster
	 * @param string $allocine
	 */
	public function __construct($id=null, $title=null, $year=null, $rating=null, $poster=null, $allocine=null) {
		$this->id = $id;
		$this->title = $title;
		$this->year = $year;
		$this->rating = $rating;
		$this->poster = $poster;
		$this->allocine = $allocine;
	}
}