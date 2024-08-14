<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

http_response_code($response_code);

if (isset($response_count)) {
	header('X-Total-Count: '. $response_count);
}

if ($response_code == 403) {
	header('X-Authenticate-Error: API-Key');
} else
if ($response_code == 401) {
	header('WWW-Authenticate: Basic');
} else
if ($response_code == 400 || $response_code == 404) {
	header('X-Error-Message: '. $response_content);
} else
if ($response_content) {
	echo $response_content;
}