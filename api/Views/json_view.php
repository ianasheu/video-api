<?php
/*-----------------------------*
 *
 * Vue pour la reponse en JSON
 *
 *-----------------------------*/

http_response_code($response_code);

header('Access-Control-Allow-Origin: *');
header('Cache-Control: private, max-age=3600, must-revalidate');
header('Content-Type: application/json; charset=UTF-8');

if ($response_code == 403) {
	header('X-Authenticate-Error: API-Key');
} else
if ($response_code == 401) {
	header('WWW-Authenticate: Basic');
} else
if ($response_code == 400) {
	header("X-Error-Message: {$response_content}");
}

if (isset($response_count)) {
	header("X-Total-Count: {$response_count}");
}

if (($response_code == 200) || ($response_code == 201)) {
	if (isset($response_content)) {
		echo $response_content;
	}	
}