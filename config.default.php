<?php
return [
	'DEBUG' => FALSE,
	'TIMEZONE' => 'Africa/Johannesburg',
	'LOCALE' => 'en_ZA.UTF8',
	'POWERED-BY' => 'Hamster coffee',
	'DATABASE' => [
		'DRIVER' => 'none',
		// INFO: none, mysql
		'HOST' => 'localhost',
		'DATABASE' => 'database',
		'USERNAME' => '',
		'PASSWORD' => '',
		'PORT' => '',
		'CHARSET' => 'utf8',
		'COLLATION' => 'utf8_general_ci',
	],
	'MEDIA' => [
		'FOLDER' => __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR,
	],
	'LOGS' => [
		'FOLDER' => __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
		'PHP' => 'php',
	],
	'GIT' => [
		'USERNAME' => '',
		'PASSWORD' => '',
		'PATH' => 'https://github.com/WilliamStam/ConanServerMonitor',
		"BRANCH" => 'master',
	],
	/* INFO: server side cacheing, templates, queries, routes etc. does not impact the asset cacheing. setting debug to true disables asset cacheing tho */
	"CACHE" => FALSE,
	/* INFO: path to the storage folder where the system will place logs / cache / temp files etc */
	'TEMP' => __DIR__ . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR,
	/* INFO: what tags are allowed in text blocks */
	"TAGS" => 'p,br,b,strong,i,italics,em,h1,h2,h3,h4,h5,h6,div,span,blockquote,pre,cite,ol,li,ul',
	/* INFO: SERVERS log file FTP */
	'FTP' => array(
		'HOST' => "",
		'PORT' => "",
		'USERNAME' => "",
		'PASSWORD' => "",
		'FOLDERS' => array(
			'LOGS' => "",
			'GAME_DB' => "",
		),
	
	),
];