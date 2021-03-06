<?php
namespace WugsTracker;

use Elementor\Plugin as Elementor_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Utils {

    public static function get_string_between($string, $start, $end){
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }

    public static function log($log) {
        if(defined('WP_DEBUG')) {
            if(WP_DEBUG) {
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( print_r( $log, true ) );
                } else {
                    error_log( $log );
                }
            }
        }        
    }

}
