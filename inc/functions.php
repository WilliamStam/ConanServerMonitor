<?php


function is_bot() {
	
	$str = preg_match('/robot|spider|crawler|curl|bot|Slurp|^$/i', $_SERVER['HTTP_USER_AGENT']);
	
	if ( $str == '1' ) {
		return TRUE; // Is a bot
	} else {
		return FALSE; // Not a bot
	}
	
	$botlist = array(
		"Teoma",
		"bingbot",
		"alexa",
		"froogle",
		"Gigabot",
		"inktomi",
		"looksmart",
		"URL_Spider_SQL",
		"Firefly",
		"NationalDirectory",
		"Ask Jeeves",
		"TECNOSEEK",
		"InfoSeek",
		"WebFindBot",
		"girafabot",
		"crawler",
		"www.galaxy.com",
		"Googlebot",
		"Googlebot",
		"Scooter",
		"Slurp",
		"msnbot",
		"appie",
		"FAST",
		"WebBug",
		"Spade",
		"ZyBorg",
		"rabaz",
		"Baiduspider",
		"Feedfetcher-Google",
		"TechnoratiSnoop",
		"Rankivabot",
		"Mediapartners-Google",
		"Sogou web spider",
		"WebAlta Crawler",
		"TweetmemeBot",
		"Butterfly",
		"Twitturls",
		"Me.dium",
		"Twiceler",
	);
	
	foreach ( $botlist as $bot ) {
		if ( strpos($_SERVER['HTTP_USER_AGENT'], $bot) !== FALSE ) {
			return TRUE;
		} // Is a bot
	}
	
	return FALSE; // Not a bot
}

function is_ajax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}


function file_size($size) {
	$unit = array(
		'b',
		'kb',
		'mb',
		'gb',
		'tb',
		'pb',
	);
	
	return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function timesince($tsmp) {
	if ( !$tsmp ) {
		return "";
	}
	$diffu = array(
		'seconds' => 2,
		'minutes' => 120,
		'hours' => 7200,
		'days' => 172800,
		'months' => 5259487,
		'years' => 63113851,
	);
	$diff = time() - strtotime($tsmp);
	$dt = '0 seconds ago';
	foreach ( $diffu as $u => $n ) {
		if ( $diff > $n ) {
			$dt = floor($diff / (.5 * $n)) . ' ' . $u . ' ago';
		}
	}
	
	return $dt;
}

function debug() {
	$args = func_get_args();
	
	$return = $args;
	$output = "screen";
	
	
	switch ( func_num_args() ) {
		case 0:
			
			exit();
			break;
		case 1:
			$return = $args[0];
			break;
	}
	
	if ( is_array($return) ) {
		$type = "array";
	} else if ( is_object($return) ) {
		$type = "array";
		
	} else {
		$type = "string";
	}
	
	
	switch ( $type ) {
		default:
			
			if ( $type == "object" ) {
				$return = json_decode(json_encode((array) $return), TRUE);
			}
			test_array($return);
			break;
		case "string":
			if ( $output == "error_log" ) {
				error_log(print_r($return, TRUE));
			} else {
				test_string($return);
			}
			break;
		
		
	}
	
	
}

function test_array($array) {
	$f3 = \Base::instance();
	if ( $f3->CLI ) {
		echo PHP_EOL.json_encode($array,JSON_PRETTY_PRINT).PHP_EOL;
		exit();
	}
	
	
	header("Content-Type: application/json");
	
	$f3->set("__testJson", TRUE);
	echo json_encode($array, JSON_PRETTY_PRINT);
	exit();
}

function test_string($array) {
	$f3 = \Base::instance();
	
	if ( $f3->CLI ) {
		echo PHP_EOL.$array.PHP_EOL;
		exit();
	}
	header("Content-Type: text/html");
	$f3->set("__testString", TRUE);
	echo $array;
	exit();
}

function bt_loop($trace) {
	if ( isset($trace['object']) ) {
		unset($trace['object']);
	}
	if ( isset($trace['type']) ) {
		unset($trace['type']);
	}
	
	
	$args = array();
	foreach ( $trace['args'] as $arg ) {
		if ( is_array($arg) ) {
			if ( count($arg) < 5 ) {
				$args[] = $arg;
			} else {
				$args[] = "Array " . count($arg);
			}
		} else {
			$args[] = $arg;
		}
	}
	$trace['args'] = $args;
	
	return $trace;
}

function rmrf($dir, $callback = FALSE) {
	foreach ( glob($dir) as $file ) {
		if ( is_dir($file) ) {
			rmrf("$file" . DIRECTORY_SEPARATOR . "*", function($file) {
				rmdir($file);
			});
		} else {
			unlink($file);
		}
		if ( is_callable($callback) ) {
			if ( file_exists($file) ) {
				$callback($file);
			}
		};
	}
}