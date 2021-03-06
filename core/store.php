<?php
namespace WugsTracker\Core;

use WugsTracker\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Store {

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
	protected static $plugin = null;
    
	private $databaseDirectory = null;
    
	private $logs_days = null;
    
	private $logs_messages = null;
    
	private $logs_activity = null;
    
	private $logs = null;

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
			self::$instance->init();
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
    public function do_hooks() {}

	/**
     * Attach Wordpress hooks
     *
     * @return void
     */
    public function init() {
        global $wugsdb; //Load a new global variable

        $databaseDirectory = trailingslashit( wp_upload_dir()['basedir'] ) . 'wugstracker-logs';
        if (!file_exists($databaseDirectory)) {
            mkdir($databaseDirectory, 0600, true);
        }

        self::$instance->databaseDirectory = $databaseDirectory;
        
		//Set the database names
        $logs = 'Logs';
		$logs_messages = 'Logs_Messages';
        $logs_activity = 'Logs_Activity';


        $wugsdb = (object) []; //Set the global variable

		$configuration = [
			"search" => [
				"min_length" => 2,
				"mode" => "or",
				"score_key" => "scoreKey",
				"algorithm" => \SleekDB\Query::SEARCH_ALGORITHM["hits"]
			]
		];

		//Load the Logs database
        self::$instance->logs = new \SleekDB\Store($logs, $databaseDirectory, $configuration);
        $wugsdb->logs = self::$instance->logs;
		//Load the Logs messages database
        self::$instance->logs_messages = new \SleekDB\Store($logs_messages, $databaseDirectory, $configuration);
        $wugsdb->logs_messages = self::$instance->logs_messages;
		//Load the Logs Activity database
        self::$instance->logs_activity = new \SleekDB\Store($logs_activity, $databaseDirectory, $configuration);
        $wugsdb->logs_activity = self::$instance->logs_activity;
    }

}
