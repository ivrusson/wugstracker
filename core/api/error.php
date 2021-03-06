<?php
namespace WugsTracker\Core\Api;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Error {
	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	public function __construct($message) {
        return new WP_Error( 'invalid',  $message);
    }
}
