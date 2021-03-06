<?php
namespace WugsTracker\Core\Api;

use WugsTracker\Utils;
use WugsTracker\Core\Api\Request;
use WugsTracker\Core\Api\Error;
use WugsTracker\Core\Api\Api_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Response {
    
    private $code = null;
    
    private $headers = [];

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
        
    }

    public function setHeader($type, $value) {
        $this->headers[$type] = $value;
        return $this;
    }

    public function status($code = null) {
        if( ! is_null($code) ) {
            $this->code = intval($code);
        }
        return $this;
    }

    public function send($input = []) {
        if( is_null($this->code) ) {
            $this->code = 200;
        }
        $res = $input;

        return $this->json_response($res);
    }

    public function error($input = null) {
        if( is_null($this->code) ) {
            $this->code = 400;
        }
        $res = (object) [];
        $res->code = $this->code;
        $res->message = new Error('invalid', $input);

        return $this->json_response($res);
    }

    private function printHeaders() {      
        header_remove(); 
        http_response_code($this->code); 
        header('Access-Control-Allow-Methods: *');
        header('Cache-Control: max-age=0, no-cache, no-store');
        header('Content-Type: application/json; charset=utf-8');
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        foreach($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }
        
        header('Status: '. http_response_code($this->code));
    }

    private function json_response($data) {
        $this->printHeaders();
        
        echo json_encode([
            'status' => $this->code < 300,
            'data' => $data
        ]);

        die;
    }
}