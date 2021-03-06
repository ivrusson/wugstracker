<?php
namespace WugsTracker\Core\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {    

    public static function randomKey($length) {
      $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
      $key = '';
      for($i=0; $i < $length; $i++) {
        $key .= $pool[mt_rand(0, count($pool) - 1)];
      }
      return $key;
    }

    public static function toArray($object) {

        $toArray = function($x) use(&$toArray) {
            return is_scalar($x) ? $x : array_map($toArray, (array) $x);
  		};

        return $toArray($object);
    }

    public static function toObject($arr) {

        $toObject = function($x) use(&$toObject) {
            return is_scalar($x) ? $x : array_map($toObject, (object) $x);
  		};

        return $toObject($arr);
    }
}
