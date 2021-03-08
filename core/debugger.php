<?php
namespace WugsTracker\Core;

use WugsTracker\Utils;
use WugsTracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Debugger {

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
	public $plugin = null;

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
			self::$instance->do_hooks();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {}

	/**
     * Attach Wordpress hooks
     *
     * @return void
     */
    public function do_hooks() {
        add_action('plugins_loaded', [self::$instance, 'on_plugin_load']);
        
        $active = get_option('wugstracker_JS_active', false);
        if($active) {
            add_action('wp_footer', [self::$instance, 'add_scripts']);
        }
    }

    public function add_scripts() {
        wp_register_script( 'wugstracker', WUGSTRACKER_URL . '/assets/js/wugstracker.js', array(), time() , 'all' );
        // Localize the script with new data
        $data = array(
            'api_endpoint' => home_url() . '/wugs/log/'
        );
        wp_localize_script( 'wugstracker', 'wugsData', $data );
        wp_enqueue_script( 'wugstracker' );

        $test_option = get_option('wugstracker_JS_test_active', false) === '1' ? true : false;
        if($test_option) {
            $inline_script = `
            console.log('Console test 1');
            console.log('Console test 2');
            console.log('Console test 3');
            thisIsNotAFunction();
            `;
            wp_add_inline_script( 'wugstracker', $inline_script );
        }
    }

    public function on_plugin_load() {
        $wp_debug = Admin::get_option('wugstracker_WP_debug');
        $wp_debug_log = Admin::get_option('wugstracker_WP_log');
        $wp_debug_display = Admin::get_option('wugstracker_WP_display');
        $error_handler = Admin::get_option('wugstracker_PHP_active');

        if($wp_debug) {
            define( 'WP_DEBUG', $wp_debug );
            define( 'WP_DEBUG_LOG', $wp_debug_log );
            define( 'WP_DEBUG_DISPLAY', $wp_debug_display);
        }

        if($error_handler) {
            self::$instance->error_handler();
        }        
    }

    public function error_handler() {
        set_error_handler (
            function($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
                self::register_error([
                    "number" => $errno,
                    "string" => $errstr,
                    "file" => $errfile,
                    "line" => $errline
                ]);
            }
        );       
    }

    public static function register_error($err) {
        $curl = curl_init();

        $data = array_merge([
            "source" => 'php'
        ], $err);
        
        curl_setopt_array($curl, [
            CURLOPT_URL => home_url() . '/wugs/log/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return;
    }

}
