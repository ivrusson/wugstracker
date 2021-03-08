<?php
namespace WugsTracker;

use WugsTracker\Core\Admin;
use WugsTracker\Core\Store;
use WugsTracker\Core\Debugger;
use WugsTracker\Core\Api\Base as Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WugsTracker plugin.
 *
 * The main plugin handler class is responsible for initializing WugsTracker. The
 * class registers and all the components required to run the plugin.
 *
 * @since 1.0.0
 */
class Plugin {
  
	public static $instance = null;

	public $admin = null;

	public $store = null;

	public $api = null;

	public $debugger = null;
  
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
			do_action( 'wugstracker/loaded' );
		}

		return self::$instance;
	}
  
	public function init() {
		$this->init_components();
    
		do_action( 'wugstracker/init' );
	}
  
	private function init_components() {
		if ( is_admin() ) {			
  		    $this->admin = Admin::instance();
		}
		// Load general classes
		$this->store = Store::instance();
		$this->api = Api::instance();
        $this->debugger = Debugger::instance();
	}

	/**
	 * Register autoloader.
	 *
	 * WugsTracker autoloader loads all the classes needed to run the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_autoloader() {
		require_once WUGSTRACKER_PATH . '/includes/autoloader.php';

		Autoloader::run();
	}

	/**
	 * Plugin constructor.
	 *
	 * Initializing WugsTracker plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->register_autoloader();

		add_action( 'init', [ $this, 'init' ], 0 );
	}

	final public static function get_title() {
		return __( 'WugsTracker', 'wugstracker' );
	}
}

Plugin::instance();
