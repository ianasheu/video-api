<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

http_response_code($response_code);

if ($response_code == 401) {
	header('WWW-Authenticate: Basic');
}

if (isset($response_count)) {
	header('X-Total-Count: '. $response_count);
}

if ($response_content) {
	echo $response_content;
}