<?php
namespace WugsTracker\Core\Api;

use WugsTracker\Plugin;
use WugsTracker\Core\Api\Router;
use WugsTracker\Core\Api\Routes\Log;
use WugsTracker\Core\Api\Routes\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Base {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
    /**
	 * Instance of Plugin class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	private $router = null;
    /**
	 * Instance of Plugin class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	private $plugin = null;

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->plugin = Plugin::instance();
			self::$instance->do_hooks();
			self::$instance->router = Router::instance();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

	}

	/**
     * Attach Wordpress hooks
     *
     * @return void
     */
    public function do_hooks() {}

	/**
     * Attach Wordpress hooks
     *
     * @return void
     */
    public function init() {

		new Options(self::$instance->router);
		new Log(self::$instance->router);
	}

}
