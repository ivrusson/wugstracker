<?php
namespace WugsTracker\Core\Api\Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options routes for Api class
 * 
 * @version 1.0.0
 */
class Options {

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	public function __construct($router) {
        $this->router = $router;
        add_action( 'wugstracker/init', array ( $this, 'init' ), 0 );
    }
    
	public function init() {
        $this->router->get('options', [$this, 'wp_admin_middleware'], [$this, 'get_options']);
        $this->router->put('options', [$this, 'wp_admin_middleware'], [$this, 'update_options']);
    }

    public function wp_admin_middleware($req, $res, $next) {
        global $current_user;
        if($current_user->ID != 0 && current_user_can('administrator')) {
            $next();            
        } else {
            $res->status(301)->error('Forbidden'); 
        }
    }

    public function get_options($req, $res) {
        $wugstracker_JS_active = get_option('wugstracker_JS_active', false) === '1' ? true : false;
        $wugstracker_PHP_active = get_option('wugstracker_PHP_active', false) === '1' ? true : false;

        $res->status(200)->send([
            "wugstracker_JS_active" => $wugstracker_JS_active,
            "wugstracker_PHP_active" => $wugstracker_PHP_active
        ]);
    }

    public function update_options($req, $res) {
        $allowed = ['wugstracker_JS_active', 'wugstracker_PHP_active', 'wugstracker_WP_debug', 'wugstracker_WP_debug_log', 'wugstracker_WP_debug_display'];
        $body = $req->body;
        $options = $body['options'] ? $body['options'] : [];

        $messages = [];
        foreach($options as $key => $value) {
            if(in_array($key, $allowed)) {
                $updated = update_option($key, $value);
                $messages[] = [
                    "type" => $updated ? 'success' : 'error',
                    "message" => $updated ? sprintf( __('Option "%s" updated correctly!', 'wugstracker'), $key) : sprintf( __('Error updating "%s" option!', 'wugstracker'), $key)
                ];
                $options[$key] = $updated == true ? $value : null;
            } else {
                $messages[] = [
                    "type" => 'error',
                    "message" => sprintf( __('Option "%s" not is a valid name!', 'wugstracker'), $key)
                ];
            }
        }

        $data = [
            "messages" => $messages,
            "options" => $options,
        ];

        $res->status(200)->send($data);
    }

    public function update_option_php($req, $res) {
        $body = $req->body;
        $value = $body['value'] ? $body['value'] : false;
        update_option('wugstracker_JS_active', $value);
        sleep(1);
        $option = get_option('wugstracker_PHP_active', false);
        $res->status(200)->send($option);
    }
}
