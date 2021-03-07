<?php
namespace WugsTracker\Core;

use WugsTracker\Utils;
use SleekDB\Store as SleekStore;
use SleekDB\Query as SleekQuery;

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
    
	private $db_dir_exists = false;
    
	private $logs = null;
    
	private $logs_messages = null;
    
	private $logs_activity = null;

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
     * Initialize funcion
     *
     * @return void
     */
    public function init() {

        $created = self::$instance->create_database_dir(); //Create database directory
        $has_perms = self::$instance->check_permissions(); //Check permissions of dir to prevent sleek db to crash

		if($created) {
			if($has_perms) {
				self::$instance->load_database();
			}
		}
    }

	private function create_database_dir() {	
        $databaseDirectory = trailingslashit( wp_upload_dir()['basedir'] ) . 'wugstracker-logs';
		
		$created = false;

        if (!file_exists($databaseDirectory)) {
            $created = mkdir($databaseDirectory, 0700, true); //Assign permissions only for propietary and group
        } else {
			$created = true;
		}

		if($created) {
        	self::$instance->db_dir_exists = true;
        	self::$instance->databaseDirectory = $databaseDirectory;
		}

		return $created;
	}

	private function check_permissions() {		
		clearstatcache(null, self::$instance->databaseDirectory);

    	return decoct( fileperms(self::$instance->databaseDirectory) & 0700 );
	}

	private function load_database() {       
        global $wugsdb; //Load a new global variable

		//Set the database names
        $logs = 'Logs';
		$logs_messages = 'Logs_Messages';
        $logs_activity = 'Logs_Activity';


        $wugsdb = (object) []; //Set the global variable

		$configuration = [ //Add search capabilities to SleekDB
			"search" => [
				"min_length" => 2,
				"mode" => "or",
				"score_key" => "scoreKey",
				"algorithm" => SleekQuery::SEARCH_ALGORITHM["hits"]
			]
		];

		//Load the Logs database
        self::$instance->logs = new SleekStore($logs, self::$instance->databaseDirectory, $configuration);
        $wugsdb->logs = self::$instance->logs;

		//Load the Logs messages database
        self::$instance->logs_messages = new SleekStore($logs_messages, self::$instance->databaseDirectory, $configuration);
        $wugsdb->logs_messages = self::$instance->logs_messages;

		//Load the Logs Activity database
        self::$instance->logs_activity = new SleekStore($logs_activity, self::$instance->databaseDirectory, $configuration);
        $wugsdb->logs_activity = self::$instance->logs_activity;
	}

}
