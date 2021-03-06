<?php
namespace WugsTracker\Core\Api;

use WugsTracker\Core\Api\Request;
use WugsTracker\Core\Api\Response;
use WugsTracker\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Router {

    /**
	 * Constant for the api endpoint.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
    protected static $endpoint = 'wugs';

    /**
	 * Constant for the WP endpoint query var.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
    protected static $endpoint_var = '__wugs';

    /**
	 * Constant for the WP endpoint query var.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
    protected static $routes_prefix = '__wugs_route_';

    /**
	 * Constant for the api endpoint.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
    protected static $allowed_methods = ['get', 'post', 'put', 'patch', 'delete'];

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
	private $routes = [];

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
      add_action( 'wugstracker/init', array ( $this, 'register_endpoints' ), 1 );
      add_filter( 'query_vars', array ( $this, 'register_query_vars' ), 0 );
      add_action( 'parse_request', array ( $this, 'parse_request' ), 0 );
    }

    /**
     * Undocumented function
     *
     * @param [type] $vars
     * @return void
     */
    public function register_query_vars( $vars ) {

      // add all the things we know we'll use
      $vars[] = self::$endpoint_var;

      //Create routes map
      foreach(self::$instance->routes as $route => $data) {
        $params = self::params_from_route($route);
        for($i=1; $i <= count($params); $i++) {
            $route_query_var = self::$routes_prefix . $i;
            $vars[] = $route_query_var;
        }
      }

      return $vars;
    }

    /**
     * Generate rewrite rules for API Endpoints
     *
     * @return void
     */
    public function register_endpoints() {

        //Generate main rewrite for base route
        add_rewrite_rule( self::build_from(), self::build_to(), 'top' );

        //Generate rewrite rules for all routes inserted into the class
        foreach(self::$instance->routes as $route => $data) {
            $params = self::params_from_route($route);
            add_rewrite_rule( self::build_from($params), self::build_to($params), 'top' );
        }
        
        //// IMPORTANT: USE ONLY IN DEVELOPMENT ////
        flush_rewrite_rules(false);
        ///////////////////////////////////////////
    }

    /**
	 * Parsing the wordpress request
	 *
	 * @param [type] $wp_query
	 * @return void
	 */
    public function parse_request($query) {
      global $wp;

      if (isset($wp->query_vars[self::$endpoint_var])) {      
        $match = self::$instance::match_route($wp->query_vars);
        if($match) {
            self::$instance::handle($match);
        } else {
            self::$instance::handle_empty();
        }
        self::$instance::handle_empty();
      }
    }

    private static function build_rule($params) {
        add_rewrite_rule( self::build_from($params), self::build_to($params), 'top' );
    }

    private static function params_from_route($str) {
        $arr = explode('/', $str);
        $deleteBars = function($v) {
            if ($v === '' || is_null($v)) {
                return false;
            }
            return true;
        };
        return array_filter($arr, $deleteBars);
    }

    private static function build_from($params = []) {
        $tranform = function($str) {
            return '/([^/]*)';
        };
        $from = '^';
        $from .= self::$endpoint;
        $from .= implode('', array_map($tranform, $params));
        $from .= '$';
        return $from;
    }

    private static function build_to($params = []) {
        $tranform = function($str) use($params) {
            $index = array_search($str, $params);
            return '&' . self::$routes_prefix . ($index+1) . '=$matches[' . ($index+1) . ']';
        };
        $to = 'index.php?';
        $to .= self::$endpoint_var . '=1';
        $to .= implode('', array_map($tranform, $params));
        return $to;
    }

    private function match_route($query_vars) {
        $map_routes = self::$instance::routes_map();
        $match = null;

        $pattern_vars = []; // Used to create a pattern from query_vars
        $qvars_to_params = []; // Used to store query_vars values for each param

        //Create an array from query var to simulate a route
        foreach($query_vars as $var => $value) {
            if(strpos($var, self::$routes_prefix) !== false) {
                $index = intval(str_replace(self::$routes_prefix, '', $var))-1;
                $pattern_vars[$index] = $value; //Example result [0 => "post", 1 => ...]
                $qvars_to_params[$index] = [
                    'query' => $var,
                    'value' => filter_var($value, FILTER_SANITIZE_STRING) //Sanitize data input to prevent bad people business
                ]; //Example result [0 => ["query" => "__wugs_route_1", "value" => "post"], ...]
            }            
        }

        //Return true if only the base route endoint query var exist 
        if(count($pattern_vars) === 0)
            return null;

        //Check all routes to
        $already_match = false;
        foreach(self::$instance->routes as $route => $data) {

            //Prevent searching in loop if already matches
            if($already_match) break;

            //Replace params that contains ":", for example :postId in /api/post/:postId 
            $params = self::params_from_route($route);
            $request_params = [];
            foreach($params as $index => $param) {
                if(!isset($pattern_vars[$index])) break;
                $qvar = $qvars_to_params[$index]['query']; //Example: __wugs_route_1
                $qvar_value = $qvars_to_params[$index]['value']; //Example: post
                if(strpos($param, ':') !== false) { //If exist replace pattern item by params index
                    $pattern_vars[$index] = $param;
                    $request_params[str_replace(':', '', $param)] = $qvar_value; //Transfor [0 => ":postId"] to ["postId" => {VALUE}]
                }
            }

            //If strings match return data
            $pattern_from_vars = implode('/', $pattern_vars);
            if($route === $pattern_from_vars) {
                //Return data needed to make the request handle
                $match = [
                    'path' => $route, //Example: /api/post/:postId 
                    'params' => $request_params, //Example: ["postId" => "12345"]
                    'methods' => self::$instance->routes[$route] //Example: ["get" => ...]
                ];
                $already_match = true;
            }
        }
        return $match;
    }

    private function routes_map() {
        $routes = [];
        foreach(self::$instance->routes as $route => $data) {
            $routes[] = $route;
        }
        return $routes;
    }

    private function handle($match) {
        extract($match); // $path, $params, $methods

        $req = new Request($params);
        $res = new Response();

        $item = $methods[strtolower($req->method)];
        if(isset($item)) {
            $fn = $item['fn'];
            $middlewares = $item['middlewares'];
            if(count($middlewares)>0) {
                $fns = array_merge($middlewares, [$fn]);
                $next = function() {
                    return;
                };  
                for($i=0; $i < count($fns); $i++) {
                    call_user_func($fns[$i], $req, $res, $next);
                }
                if(isset($fns[$i])) {
                    call_user_func($fns[$i], $req, $res);
                }      
            } else {
                call_user_func($fn, $req, $res);
            }
        } else {
            $this->handle_empty();
        }
    }

    private function handle_empty() {
        $req = new Request();
        $res = new Response();
        $res->status(401)->send('Unauthorized request!');
    }

    public function get() {
        call_user_func_array([self::$instance, 'add'], array_merge(['get'], func_get_args()));
    }

    public function post() {
        call_user_func_array([self::$instance, 'add'], array_merge(['post'], func_get_args()));
    }

    public function put() {
        call_user_func_array([self::$instance, 'add'], array_merge(['put'], func_get_args()));
    }

    public function patch() {
        call_user_func_array([self::$instance, 'add'], array_merge(['patch'], func_get_args()));
    }

    public function delete() {
        call_user_func_array([self::$instance, 'add'], array_merge(['delete'], func_get_args()));
    }

    public function add() {
        $count = func_num_args();

        if($count >= 3) {
            $method = func_get_arg(0);
            $path = func_get_arg(1);
            $fn = func_get_arg($count-1);
            if(is_string($path) && in_array(strtolower($method), self::$allowed_methods) && is_callable($fn)) {

                $middlewares = [];
                if($count > 3) {
                    for($i=2; $i<$count && ($count-1)>$i; $i++) {
                        $mfn = func_get_arg($i);
                        if(is_callable($mfn)) {
                            $middlewares[] = $mfn;
                        }
                    }
                }
                
                if(!isset(self::$instance->routes[$path])) {
                    self::$instance->routes[$path] = [];
                }

                self::$instance->routes[$path][$method] = [
                    "middlewares" => $middlewares,
                    "fn" => $fn
                ];

                return true;
            }
        }
    
        return null;
    }

}
