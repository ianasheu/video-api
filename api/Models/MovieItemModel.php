<?php
/*---------------------*
 *
 * Modele pour un film
 *
 *---------------------*/

declare(strict_types=1);

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
	public int $id;
	public string $title;
	public ?int $year;
	public ?float $rating;
	public ?string $poster;
	public ?string $allocine;

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
	public function __construct(int $id, string $title, ?int $year=null, ?float $rating=null, ?string $poster=null, ?string $allocine=null) {
		$this->id = $id;
		$this->title = $title;
		$this->year = $year;
		$this->rating = $rating;
		$this->poster = $poster;
		$this->allocine = $allocine;
	}
}