<?php
require_once('../bootstrap.php');

/* Call the routes for each app */
(new \app\web\_())->routes();


/* INFO: assets routes */
$f3->route("GET /assets/@version/@app/*", function($app, $params) {
	$path = trim("../app/" . $params['app'] . "/ui/" . $params['*']);
	
	echo output::asset($path);
	exit();
});

$f3->route("GET /assets/@version/public/*", function($app, $params) {
	$path = trim("../public/" . $params['*']);
	
	echo output::asset($path);
	exit();
});
$f3->route("GET /vendor/*", function($app, $params) {
	$path = trim("../vendor/" . $params['*']);
	
	
//	debug($path);
	echo output::asset($path);
	exit();
});

$f3->route("GET /php", function($app, $params) {
	
	echo phpinfo();
	exit();
});


(new bootstrap\app())->run();
