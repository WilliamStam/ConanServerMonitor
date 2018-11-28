<?php
class output {
	static function string($str){
		$f3 = \Base::instance();

		return $f3->OUTPUT['RENDERED'] = $str;
	}
	static function json($array){
		$f3 = \Base::instance();
		$f3->set("__runJSON", TRUE);

		return $f3->OUTPUT['RESPONSE'] = $array;
	}

	static function asset($path){
		$f3 = \Base::instance();
		if (!file_exists($path)){
			$f3->error(404);
		}
		$version = $f3->get("BUILD");
		$etag = md5($version . "|" . $path);


		$mimetype = strings::get_mime_type($path);



		if ($f3->get("DEBUG")) {
			/* INFO: never cache the asset since debug is enabled*/
			$ts = gmdate("D, d M Y H:i:s") . " GMT";
			header("Expires: $ts");
			header("Last-Modified: $ts");
			header("Pragma: no-cache");
			header("Cache-Control: no-cache, must-revalidate");
		} else {
			/* INFO: since debug isnt enabled - prod? - we need to cache it if its changed */

			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == '"'.$etag.'"') {
				header('HTTP/1.1 304 Not Modified');
				header_remove("Cache-Control");
				header_remove("Pragma");
				header_remove("Expires");
				exit();
			}
			header('Etag: "'.$etag.'"');

			$seconds_to_cache = ((60 * 60) * 24) * 7 // 7 day cache
			;
			$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
			header("Expires: $ts");
			header("Pragma: cache");
			header("Cache-Control: max-age=$seconds_to_cache");


		}






		header("Content-type: ". $mimetype. "; charset: UTF-8", true);
		
//		debug($path);
		$contents = file_get_contents($path);
		$contents = str_replace("../../vendor/components/jqueryui/themes/base/images/", "/assets/@VERSION/public/images/", $contents);
		$contents = str_replace("@VERSION", $version, $contents);
		
		
		
		
		return $contents;
	}

}