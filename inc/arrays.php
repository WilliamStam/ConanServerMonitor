<?php

class arrays {
	static function array_key_index($key, $array) {
		$return = array();
		foreach ( $array as $item ) {
			$return[$item[$key]] = $item;
		}
		
		return $return;
	}
	
	static function magic_array_merge($array1, $array2) {
		
		foreach ( $array1 as $key => $value ) {
			if ( isset($array2[$key]) ) {
				if ( is_numeric($key) ) {
					$array1[] = $array2[$key];
				} else if ( is_array($value) ) {
					$array1[$key] = self::magic_array_merge($value, $array2[$key]);
				} else {
					$array1[$key] = $array2[$key];
				}
				unset($array2[$key]);
			}
		}
		
		
		return $array1;
	}
	
	
	static function arrayRecursiveDiff($aArray1, $aArray2) {
		$aReturn = array();
		
		foreach ( $aArray1 as $mKey => $mValue ) {
			if ( array_key_exists($mKey, $aArray2) ) {
				if ( is_array($mValue) ) {
					$aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
					if ( count($aRecursiveDiff) ) {
						$aReturn[$mKey] = $aRecursiveDiff;
					}
				} else {
					if ( $mValue != $aArray2[$mKey] ) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}
		
		return $aReturn;
	}
	
}