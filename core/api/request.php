<?php
namespace WugsTracker\Core\Api;

use WugsTracker\Core\Api\Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Request {

    public $method = null;

    public $headers = null;

    public $query = null;

    public $params = null;

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	public function __construct($params = []) {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = $this->getHeaders();
        $this->params = $this->getParams($params);
        $this->query = $this->getQuery();
        $this->body = $this->getBody();
    }

    private static function getHeaders() {
        if (!function_exists('getallheaders')) {
            $getallheaders = function() {
                $headers = [];
                foreach ($_SERVER as $key => &$value) {
                    $name = self::sanitize($key);
                    $value = self::sanitize($value);
                    if (substr($name, 0, 5) == 'HTTP_')
                    {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            };
            return $getallheaders();
        }
        return getallheaders();
    }

    private static function getQuery() {
        return isset($_GET) ? self::sanitize($_GET) : [];
    }

    private static function getParams($params) {
        return isset($params) ? self::sanitize($params) : [];
    }

    private static function getBody() {
        $data = [];

        if(isset($_SERVER['CONTENT_TYPE'])) {
            $php_input = file_get_contents('php://input');

            if(isset($data)) {
                if(strpos('application/json', $_SERVER['CONTENT_TYPE']) !== false) {
                   $data = json_decode($php_input, true);
                }
                if(strpos('multipart/form-data', $_SERVER['CONTENT_TYPE']) !== false) {
                    $data = parse_str($php_input, true);
                }
                $data = self::sanitize($data);
            }
            
        }

        return $data;
    }

    private static function sanitize($data) {
        if(is_array($data) || is_object($data)) {
            foreach ($data as &$value) {
                if (is_scalar($value)) {
                    $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
                    continue;
                }

                $value = self::sanitize($value);
            }
        }
        if(is_callable($data)) {
            $data = '';
        }
        if(is_scalar($data)) {
            $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        }   

        return $data;
    }


}