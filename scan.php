#!/usr/bin/php
<?php

namespace {
	require_once('bootstrap.php');
	
	
//	(new \scanner\scan())->getLogs();
		(new \scanner\scan())->logs();
}

namespace scanner {
	
	use \strings;
	use \arrays;
	use \timer;
	use Touki\FTP\Connection\Connection;
	use Touki\FTP\FTPWrapper;
	
	class scan {
		private $cfg;
		public $data;
		
		function __construct() {
			
			$this->f3 = \Base::instance();
			$this->cfg = $this->f3->get("CFG");
			
			$this->playersIndex = array();
			$this->ips = array();
			
			$this->tables();
			$this->getPlayers();
			
		}
		
		function __destruct() {
		
		
		}
		
		function tables() {
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM scans LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `scans` (  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  	`daykey` VARCHAR(8) DEFAULT NULL,
	`timestamp` DATETIME DEFAULT NULL,
  `msg` TEXT DEFAULT NULL,
	`deleted` TINYINT(1) DEFAULT 0,
	`log` LONGTEXT DEFAULT NULL , PRIMARY KEY (`ID`), INDEX (`deleted`), INDEX (`daykey`)) ENGINE = InnoDB;");
			}
			
			
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM chats LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `chats` (  `ID` INT(11) NOT NULL AUTO_INCREMENT,
	`msgkey` VARCHAR(50) DEFAULT NULL,
  	`daykey` VARCHAR(8) DEFAULT NULL,
	`timestamp` DATETIME DEFAULT NULL,
  `playerID` INT(11) DEFAULT NULL,
  `msg` TEXT DEFAULT NULL,
  `lastscan` DATETIME DEFAULT NULL,
	`deleted` TINYINT(1) DEFAULT 0,
	`log` LONGTEXT DEFAULT NULL , PRIMARY KEY (`ID`), UNIQUE  KEY (`msgkey`), INDEX (`deleted`), INDEX (`daykey`), INDEX (`playerID`)) ENGINE = InnoDB;");
			}
			
			
			
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM ips LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `ips` (  `ID` INT(11) NOT NULL AUTO_INCREMENT,
	`msgkey` VARCHAR(50) DEFAULT NULL,
  	`daykey` VARCHAR(8) DEFAULT NULL,
	`timestamp` DATETIME DEFAULT NULL,
  `playerID` INT(11) DEFAULT NULL,
  `ip` VARCHAR(200) DEFAULT NULL,
  `port` VARCHAR(100) DEFAULT NULL,
  `lastscan` DATETIME DEFAULT NULL,
	`deleted` TINYINT(1) DEFAULT 0,
	`log` LONGTEXT DEFAULT NULL , PRIMARY KEY (`ID`), UNIQUE  KEY (`msgkey`), INDEX (`deleted`), INDEX (`daykey`), INDEX (`playerID`)) ENGINE = InnoDB;");
			}
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM steamids LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `steamids` (  `ID` INT(11) NOT NULL AUTO_INCREMENT,
	`msgkey` VARCHAR(50) DEFAULT NULL,
  	`daykey` VARCHAR(8) DEFAULT NULL,
	`timestamp` DATETIME DEFAULT NULL,
  `playerID` INT(11) DEFAULT NULL,
  `steamid` VARCHAR(200) DEFAULT NULL,
  `lastscan` DATETIME DEFAULT NULL,
	`deleted` TINYINT(1) DEFAULT 0,
	`log` LONGTEXT DEFAULT NULL , PRIMARY KEY (`ID`), UNIQUE  KEY (`msgkey`), INDEX (`deleted`), INDEX (`daykey`), INDEX (`playerID`)) ENGINE = InnoDB;");
			}
			
			
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM players LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `players` (  `ID` INT(11) NOT NULL AUTO_INCREMENT,
	`timestamp` DATETIME DEFAULT NULL,
	`player` VARCHAR(200) DEFAULT NULL,
  	 `lastscan` DATETIME DEFAULT NULL,
	`deleted` TINYINT(1) DEFAULT 0,
	`log` LONGTEXT DEFAULT NULL , PRIMARY KEY (`ID`), UNIQUE  KEY (`player`), INDEX (`deleted`)) ENGINE = InnoDB;");
			}
			
		}
		function getLogs() {
			
			
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
			
			echo "getting a list of log files:\n";
			$remoteFolder = $this->cfg['FTP']['FOLDERS']['LOGS'];
			$logs = $wrapper->rawlist($remoteFolder);
			
			$files = array();
			
			foreach ( $logs as $line ) {
				if ( strpos($line, "ConanSandbox-backup") ) {
					$file = substr($line, strpos($line, "ConanSandbox-backup"));
					
					$status = "Ok";
					if ( !in_array($file, $scanned_directory) ) {
						$status = "Needed";
						$files[] = $file;
					}
					
					
					echo "  - " . $file . " - " . $status . PHP_EOL;
					
				}
				if ( strpos($line, "ConanSandbox.log") ) {
					$file = substr($line, strpos($line, "ConanSandbox.log"));
					
					$status = "Needed";
					$files[] = $file;
					
					echo "  - " . $file . " - " . $status . PHP_EOL;
					
				}
				if ( strpos($line, "ServerCommandLog") ) {
					$file = substr($line, strpos($line, "ServerCommandLog"));
					
					$status = "Ok";
					if ( !in_array($file, $scanned_directory) ) {
						$status = "Needed";
						$files[] = $file;
					}
					
					
					echo "  - " . $file . " - " . $status . PHP_EOL;
					
				}
				
			}
			
			if ( count($files) ) {
				echo "Downloading needed files" . PHP_EOL;
				
				foreach ( $files as $file ) {
					echo "  - downloading {$file} ";
					$wrapper->get($folder . $file, $remoteFolder . "/" . $file);
					echo " done" . PHP_EOL;
					
				}
				
			}
			
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
		
		function logs() {
			
			$folder = $this->cfg['MEDIA']["FOLDER"] . "logfiles" . DIRECTORY_SEPARATOR;
			$scanned_directory = array_values(array_map(function($file) {
				return $file;
			}, array_diff(scandir($folder), array(
				'..',
				'.',
			))));
			
			
			
			
			
			
			
			$this->chatTable = new \DB\SQL\Mapper($this->f3->get("DB"), 'chats');
			
			
			foreach ( $scanned_directory as $file ) {
				
				if ( strpos($file, "ConanSandbox") === 0 ) {
					echo " - {$file}" . PHP_EOL;
					$this->_scan_log_file($folder . $file);
				}
				
				
			}
			
			debug($this->ips);
			echo "Done";
			
			
		}
		
		function getPlayers() {
			$this->players = $this->f3->get("DB")->exec("SELECT * FROM players");
			$this->playersIndex = arrays::array_key_index("player", $this->players);
		}
		function player($player) {
			
			
			if ( isset($this->playersIndex[$player]) ) {
				return $this->playersIndex[$player]['ID'];
			} else {
				$this->f3->get("DB")->exec("INSERT INTO players (`player`,`timestamp`) VALUES (:player,now()) ON DUPLICATE KEY UPDATE lastscan = CURRENT_TIMESTAMP;", array(
					":player" => $player,
				));
				
				$this->getPlayers();
				
				return $this->playersIndex[$player]['ID'];
			}
			
		}
		
		function _scan_log_file($file) {
			$file = new \SplFileObject($file);
			
			// Loop until we reach the end of the file.
			while ( !$file->eof() ) {
				// Echo one line from the file.
				
				$line = trim($file->fgets());
				$timestamp = substr($line, 1, 23);
				$key = $timestamp;
				$key = preg_replace("/[^0-9]/", "", $key);
				
				if ( is_numeric($key) ) {
					
					if ( strpos($line, "BattlEyeLogging") > 0 ) {
						//						echo strpos($line, "BattlEyeLogging")."\t".$line.PHP_EOL;
						$this->_extract_IP_info($line, $key, $timestamp);
					}
					if ( strpos($line, "ChatWindow") > 0 ) {
						//						echo strpos($line, "BattlEyeLogging")."\t".$line.PHP_EOL;
						$this->_extract_chat_info($line, $key, $timestamp);
					}
					
					
				}
				
				
			}
			
			// Unset the file to call __destruct(), closing the file handle.
			$file = NULL;
			
			
		}
		
		function _extract_IP_info($line, $key, $timestamp) {
			$datetimetimestamp = date_create_from_format('Y.m.d-H.i.s:u', $timestamp);
			
			
			$line = trim($line);
			
			preg_match('/Player (\#[0-9] *) (.+) (\((.+)\:(.+)\)|disconnected)/', $line, $connected);
			
			if ( isset($connected[4]) ) {
				$PLAYERid = $this->player($connected[2]);
				
				$key = md5(  $PLAYERid. "|" . $connected[4]);
				
				$this->f3->get("DB")->exec("INSERT INTO ips (`msgkey`,`daykey`,`timestamp`,`playerID`,`ip`) VALUES (:msgkey,:daykey,:timestamp,:playerID,:ip) ON DUPLICATE KEY UPDATE lastscan = CURRENT_TIMESTAMP;", array(
					":msgkey" => $key,
					":daykey" => $datetimetimestamp->format("Ymd"),
					":timestamp" => $datetimetimestamp->format("Y-m-d H:i:s"),
					":playerID" => $PLAYERid,
					":ip" => $connected[4],
				));
				
				$this->ips[$connected[2]][] = $line;
			}
			
			
			
			
			
			
			preg_match('/SteamID (.*) and name \'(.*)\'/', $line, $d);
			if ( isset($d[1]) ) {
			//	$this->steamid[$d[1]][$d[2]][] = $timestamp;
				
				$values = array(
					"timestamp" => $datetimetimestamp->format("Y-m-d H:i:s"),
					"daykey" => $datetimetimestamp->format("Ymd"),
					"player" => $d[2],
					"steamid" => $d[1],
					"msgkey" => md5( $this->player($d[2])."|".$d[1]),
				);
				
				$this->f3->get("DB")->exec("INSERT INTO steamids (`msgkey`,`daykey`,`timestamp`,`playerID`,`steamid`) VALUES (:msgkey,:daykey,:timestamp,:playerID,:steamid) ON DUPLICATE KEY UPDATE lastscan = CURRENT_TIMESTAMP;", array(
					":msgkey" => $values['msgkey'],
					":daykey" => $values['daykey'],
					":timestamp" => $values['timestamp'],
					":playerID" => $this->player($values['player']),
					":steamid" => $values['steamid']
				));
				
				
//				debug($d);
				
			}
			
			
		}
		
		function _extract_chat_info($line, $key, $timestamp) {
			
			$line = trim($line);
			
//			debug($line);
			preg_match('/Character (.+) said\: (.*)/', $line, $connected);
			
			$datetimetimestamp = date_create_from_format('Y.m.d-H.i.s:u', $timestamp);
			
			
			$values = array(
				"timestamp" => $datetimetimestamp->format("Y-m-d H:i:s"),
				"daykey" => $datetimetimestamp->format("Ymd"),
				"player" => $connected[1],
				"msg" => $connected[2],
				"msgkey" => md5($timestamp . "|" . $connected[1] . "|" . $connected[2]),
			);
//			debug($values);
			$this->f3->get("DB")->exec("INSERT INTO chats (`msgkey`,`daykey`,`timestamp`,`playerID`,`msg`) VALUES (:msgkey,:daykey,:timestamp,:playerID,:msg) ON DUPLICATE KEY UPDATE lastscan = CURRENT_TIMESTAMP;", array(
				":msgkey" => $values['msgkey'],
				":daykey" => $values['daykey'],
				":timestamp" => $values['timestamp'],
				":playerID" => $this->player($values['player']),
				":msg" => $values['msg'],
			));
			
			
		}
	}
}






