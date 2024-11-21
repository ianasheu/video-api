<?php
/*--------------------------------------*
 *
 * Liste des identifiants/mots de passe
 * necessaire a l utilisation de l API
 *
 *--------------------------------------*/

if (!defined('READ_ACCESS')) {
	define('READ_ACCESS', 1);
}
if (!defined('WRITE_ACCESS')) {
	define('WRITE_ACCESS', 2);
}
if (!defined('USERS')) {
	define('USERS', [
		[
			'login' => 'admin',
			'password' => 'password',
			'access' => WRITE_ACCESS,
			'api_key' => 'xxxx-xxxx-xxxx-xxxx'
		],
		[
			'login' => 'user',
			'password' => 'password',
			'access' => READ_ACCESS,
			'api_key' => 'video-api-public-key'
		]
	]);
}
