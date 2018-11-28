<?php

include_once "updater.php";
$updaterO = new \update\updater();

$updaterO->run();

function test($input) {
	if (is_array($input)){
		header("Content-Type: application/json");
		echo json_encode($input);
	} else {
		header("Content-Type: text/html");
		echo $input;
	}

	exit();
}