<?php

class strings {
	static function str_rreplace($search, $replace, $subject) {
		$pos = strrpos($subject, $search);
		
		if ( $pos !== FALSE ) {
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		
		return $subject;
	}
	
	static function str_lreplace($search, $replace, $subject) {
		$pos = strpos($subject, $search);
		
		if ( $pos !== FALSE ) {
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		
		return $subject;
	}
	
	static function uuid() {
		return \Ramsey\Uuid\Uuid::uuid4()->__toString();
	}
	
	static function numberHash($str) {
		return hexdec(substr(sha1($str), 0, 15));
	}
	
	static function highlight($needle, $haystack) {
		$ind = stripos($haystack, $needle);
		$len = strlen($needle);
		if ( $ind !== FALSE ) {
			return substr($haystack, 0, $ind) . '<span class="highlight">' . substr($haystack, $ind, $len) . '</span>' . self::highlight($needle, substr($haystack, $ind + $len));
		} else {
			return $haystack;
		}
	}
	
	static function toAscii($str, $replace = array(), $delimiter = '-') {
		if ( !empty($replace) ) {
			$str = str_replace((array) $replace, ' ', $str);
		}
		
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		
		return $clean;
	}
	
	static function get_mime_type($filename) {
		$idx = explode('.', $filename);
		$count_explode = count($idx);
		$idx = strtolower($idx[$count_explode - 1]);
		
		$mimet = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',
			
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			
			// fonts
			'ttf' => 'font/ttf',
			'eot' => 'font/eot',
			'otf' => 'font/otf',
			'woff' => 'font/woff',
			'woff2' => 'font/woff2',
		);
		
		if ( isset($mimet[$idx]) ) {
			return $mimet[$idx];
		} else {
			return 'application/octet-stream';
		}
	}
	
	static function log($class="",$method="",$text="",$colour=false) {
		
		$text = (new \DateTime())->format("Y-m-d H:i:s.v")."\t[" . $class."->".$method."]\t". $text;
		
		
		
		echo $text . PHP_EOL;
	}
	
	static function checkEmail($value) {
		$return = false;
		if ( $value ) {
			$return = true;
		}
		
		return $return;
	}
	
	static function checkNumber($value) {
		$return = false;
		if ( $value ) {
			$return = true;
		}
		
		return $return;
	}
	
	static function timestamp($timestamp) {
		$timestamp = strtotime($timestamp);
		return array(
			"raw" => $timestamp,
			"day" => array(
					"d"=>date("d",$timestamp),
					"D"=>date("D",$timestamp),
					"l"=>date("l",$timestamp),
			),
			"month" => array(
					"F"=>date("F",$timestamp),
					"M"=>date("M",$timestamp),
					"m"=>date("m",$timestamp),
			),
			"year" => array(
					"y"=>date("y",$timestamp),
					"Y"=>date("Y",$timestamp),
			),
			"time" => array(
				"H"=>date("H",$timestamp),
				"i"=>date("i",$timestamp),
				"s"=>date("s",$timestamp),
			)
		);
		
		
		
		
		
		
	}
	
	
	
}