#!/usr/bin/php
<?php

namespace {
	require_once('bootstrap.php');
	
	
	(new \scanner\scan())->getLogs();
//	(new \scanner\scan())->logs();
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
			
			$this->ips = array();
			$this->steamid = array();
			$this->chat = array();
			
			
		
			
		}
		
		function __destruct() {
		
		
		}
		
		function getLogs() {
			
			$host = $this->cfg['FTP']['HOST'];
			$port = $this->cfg['FTP']['PORT'];
			$user = $this->cfg['FTP']['USERNAME'];
			$pass = $this->cfg['FTP']['PASSWORD'];
			
			
			
			
			$folder = $this->cfg['MEDIA']["FOLDER"] . "logfiles" . DIRECTORY_SEPARATOR;
			$scanned_directory = array_values(array_map(function($file) {
				return $file;
			}, array_diff(scandir($folder), array(
				'..',
				'.',
			))));
			
			
			
			
			echo "connecting to FTP: {$host}:{$port}\n";
			
			$connection = new Connection($host, $user, $pass,$port);
			$connection->open();
			
			$wrapper = new FTPWrapper($connection);
			$wrapper->pasv(true);
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
					
					
					echo "  - ".$file ." - ".$status.PHP_EOL;
					
				}
				if ( strpos($line, "ConanSandbox.log") ) {
					$file = substr($line, strpos($line, "ConanSandbox.log"));
					
					$status = "Needed";
					$files[] = $file;
					
					echo "  - ".$file ." - ".$status.PHP_EOL;
					
				}
				if ( strpos($line, "ServerCommandLog") ) {
					$file = substr($line, strpos($line, "ServerCommandLog"));
					
					$status = "Ok";
					if ( !in_array($file, $scanned_directory) ) {
						$status = "Needed";
						$files[] = $file;
					}
					
					
					echo "  - ".$file ." - ".$status.PHP_EOL;
					
				}
				
			}
			
			if ( count($files) ) {
				echo "Downloading needed files" . PHP_EOL;
				
				foreach ( $files as $file ) {
					echo "  - downloading {$file} ";
					$wrapper->get($folder . $file, $remoteFolder . "/". $file);
					echo " done".PHP_EOL;
					
				}
				
			}
			
			echo "Closing FTP".PHP_EOL;
			
			
			//var_dump($files);
			
			$connection->close();
			
			
			echo "Starting scan:" . PHP_EOL;
			
			$this->logs();
			
			
			
		}
		
		function logs() {
			
			$folder = $this->cfg['MEDIA']["FOLDER"] . "logfiles" . DIRECTORY_SEPARATOR;
			$scanned_directory = array_values(array_map(function($file) {
				return $file;
			}, array_diff(scandir($folder), array(
				'..',
				'.',
			))));
			
			
			try {
				$this->f3->get("DB")->exec("SELECT ID FROM chats LIMIT 0,1");
				
			} catch ( \PDOException $e ) {
				$this->f3->get("DB")->exec("CREATE TABLE `chats` (  `ID` int(11) NOT NULL AUTO_INCREMENT,
	`msgkey` varchar(50) DEFAULT NULL,
  	`daykey` varchar(8) DEFAULT NULL,
	`timestamp` datetime DEFAULT NULL,
  `player` varchar(200) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `lastscan` datetime DEFAULT NULL,
	`deleted` tinyint(1) DEFAULT 0,
	`log` longtext DEFAULT NULL , PRIMARY KEY (`ID`), UNIQUE  KEY (`msgkey`), INDEX (`deleted`), INDEX (`daykey`)) ENGINE = InnoDB;");
			}
			
			
			
			$this->chatTable = new \DB\SQL\Mapper($this->f3->get("DB"), 'chats');
			
			
			
			
			
			
			
			foreach ( $scanned_directory as $file ) {
				
				if ( strpos($file, "ConanSandbox") === 0 ) {
					echo " - {$file}".PHP_EOL;
					$this->_scan_log_file($folder.$file);
				}
				
			
			}
			
			
			echo "Done, writing log files to ".$this->cfg['MEDIA']["FOLDER"].PHP_EOL;
			
//			file_put_contents($this->cfg['MEDIA']["FOLDER"]."chat.log",implode(PHP_EOL,$this->chat));
			file_put_contents($this->cfg['MEDIA']["FOLDER"]."ips.log",json_encode($this->ips,JSON_PRETTY_PRINT));
			file_put_contents($this->cfg['MEDIA']["FOLDER"]."steamids.log",json_encode($this->steamid,JSON_PRETTY_PRINT));
			
			
			//debug($this->steamid,$this->chat,$this->ips);
			
			
		}
		
		function _scan_log_file($file) {
			$file = new \SplFileObject($file);
			
			// Loop until we reach the end of the file.
			while (!$file->eof()) {
				// Echo one line from the file.
				
				$line = trim($file->fgets());
				$timestamp = substr($line, 1, 23);
				$key = $timestamp;
				$key = preg_replace("/[^0-9]/", "", $key);
				
				if ( is_numeric($key) ) {
					
					if ( strpos($line, "BattlEyeLogging") > 0) {
//						echo strpos($line, "BattlEyeLogging")."\t".$line.PHP_EOL;
						$this->_extract_IP_info($line,$key,$timestamp);
					}
					if ( strpos($line, "ChatWindow") > 0) {
//						echo strpos($line, "BattlEyeLogging")."\t".$line.PHP_EOL;
						$this->_extract_chat_info($line,$key,$timestamp);
					}
					
				
				
					
				}
				
			
				
				
				
				
			}
			
			// Unset the file to call __destruct(), closing the file handle.
			$file = null;
			
			
			
		
		
		
		}
		
		function _extract_IP_info($line,$key,$timestamp) {
			
			$line = trim($line);
			
			preg_match('/Player (\#[0-9].*) (.+) (\((.+)\:(.+)\)|disconnected)/', $line, $connected);
			
			if ( isset($connected[4]) ) {
					$this->ips[$connected[4]][$connected[2]][] = $timestamp;
				
			}
			
			
			preg_match('/SteamID (.*) and name \'(.*)\'/', $line, $d);
			if ( isset($d[1]) ) {
				$this->steamid[$d[1]][$d[2]][] = $timestamp;
			}
			
			
			
			
			
			
			
		}
		function _extract_chat_info($line,$key,$timestamp) {
			
			$line = trim($line);
			
			preg_match('/Character (.+) said\: (.*)/', $line, $connected);
			
			$datetimetimestamp = date_create_from_format('Y.m.d-H.i.s:u', $timestamp);
			
		
			
			$values = array(
				"timestamp" => $datetimetimestamp->format("Y-m-d H:i:s"),
				"daykey" => $datetimetimestamp->format("Ymd"),
				"player" => $connected[1],
				"msg" => $connected[2],
				"msgkey"=>md5($timestamp."|".$connected[1]."|".$connected[2])
			);
			
			$this->f3->get("DB")->exec("INSERT INTO chats (`msgkey`,`daykey`,`timestamp`,`player`,`msg`) VALUES (:msgkey,:daykey,:timestamp,:player,:msg) ON DUPLICATE KEY UPDATE lastscan = CURRENT_TIMESTAMP;", array(
					":msgkey" => $values['msgkey'],
					":timestamp" => $values['timestamp'],
					":daykey" => $values['daykey'],
					":player" => $values['player'],
					":msg" => $values['msg'],
				)
			);
			
			
			
			
			
			
			
			
			
			
		}
	}
}






