<?php
/*---------------------*
 *
 * Modele pour un film
 *
 *---------------------*/

namespace api\Models;

class MovieItemModel {

	// Propriétés
	public $id;
	public $title;
	public $year;
	public $rating;
	public $poster;
	public $allocine;

	/*
	 * Constructeur
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