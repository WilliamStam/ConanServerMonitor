#!/usr/bin/php
<?php

namespace {
	require_once('bootstrap.php');
	
	//debug($GLOBALS['CFG']);
	
	if ( $GLOBALS['CFG']['local'] ) {
		(new \scanner\db())->scanDB();
	} else {
		(new \scanner\db())->getDB();
	}
	
	
}

namespace scanner {
	
	use Touki\FTP\Connection\Connection;
	use Touki\FTP\FTPWrapper;
	
	class db {
		private $cfg;
		public $data;
		
		function __construct() {
			
			$this->f3 = \Base::instance();
			$this->cfg = $this->f3->get("CFG");
			
		
			
		}
		
		function __destruct() {
		
		
		}
		
		
		function getDB() {
			
			
			$host = $this->cfg['FTP']['HOST'];
			$port = $this->cfg['FTP']['PORT'];
			$user = $this->cfg['FTP']['USERNAME'];
			$pass = $this->cfg['FTP']['PASSWORD'];
			
			
			$folder = $this->cfg['MEDIA']["FOLDER"] . "logfiles" . DIRECTORY_SEPARATOR;
			
			if ( !file_exists($folder) ) {
				@mkdir($folder, 01777, TRUE);
			}
			
			
			$scanned_directory = array_values(array_map(function($file) {
				return $file;
			}, array_diff(scandir($folder), array(
				'..',
				'.',
			))));
			
			
			echo "connecting to FTP: {$host}:{$port}\n";
			
			$connection = new Connection($host, $user, $pass, $port);
			$connection->open();
			
			$wrapper = new FTPWrapper($connection);
			$wrapper->pasv(TRUE);
			//			$wrapper->chdir("197.189.254.122_17000");
			
			$remoteFolder = $this->cfg['FTP']['FOLDERS']['GAME_DB'];
			
			
			$handle = fopen($remoteFolder . "/" . "game_backup_1.db", 'w+');
			$ftp->download($handle, $file);
			
			
			
			
			echo "  - downloading game_backup_1.db ";
			$wrapper->get($folder . "game_backup_1.db", $remoteFolder . "/" . "game_backup_1.db");
			
			
			
			echo "Closing FTP" . PHP_EOL;
			
			
			//var_dump($files);
			
			$connection->close();
			
			
			echo "Starting scan:" . PHP_EOL;
			
			$this->logs();
			
			
			$this->f3->get("DB")->exec("INSERT INTO scans (daykey,timestamp,msg) VALUES (:daykey,:timestamp,:msg);", array(
				":daykey" => date("Ymd"),
				":timestamp" => date("Y-m-d H:i:s"),
				":msg" => "scan.php",
			));
			
			
		}
		
		function scanDB() {
		
		
		}
	}
	
	
}






