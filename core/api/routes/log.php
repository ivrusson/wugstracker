<?php
namespace WugsTracker\Core\Api\Routes;

use WugsTracker\Utils;
use WugsTracker\Core\Api\Utils as ApiUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log {
	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	public function __construct($router) {
        $this->router = $router;
        add_action( 'wugstracker/init', array ( $this, 'init' ), 0 );
        add_action( 'wugstracker/init', array ( $this, 'nonce' ), 0 );
    }
    
	public function init() {

        // LOGS DB
        $this->router->post('log', [$this, 'nonce_middleware'], [$this, 'save_log']);
        $this->router->get('log/:logId', [$this, 'wp_admin_middleware'], [$this, 'get_log']);
        $this->router->put('log/:logId', [$this, 'wp_admin_middleware'], [$this, 'update_log']);
        $this->router->delete('log/:logId', [$this, 'wp_admin_middleware'], [$this, 'remove_log']);
        $this->router->get('logs', [$this, 'wp_admin_middleware'], [$this, 'find_logs']);
        $this->router->get('logsCount', [$this, 'wp_admin_middleware'], [$this, 'logs_count']);

        // LOGS ACTIVITY DB
        $this->router->get('log/activity', [$this, 'wp_admin_middleware'], [$this, 'find_log_activity']);

        // LOGS MESSAGES DB
        $this->router->post('log/message', [$this, 'wp_admin_middleware'], [$this, 'save_log_message']);
        $this->router->put('log/message', [$this, 'wp_admin_middleware'], [$this, 'update_log_message']);
        $this->router->delete('log/message', [$this, 'wp_admin_middleware'], [$this, 'remove_log_message']);
        $this->router->get('log/messages', [$this, 'wp_admin_middleware'], [$this, 'find_log_messages']);
    }
    
	public function nonce() {
        setcookie('wugstracker-nonce', ApiUtils::randomKey(25), time() + 86400, "/"); // 86400 = 1 day
    }

    public static function nonce_middleware($req, $res, $next) {
        $nonceHeader = isset($req->headers['X-Wp-Js-Debugger-Nonce']) ? $req->headers['X-Wp-Js-Debugger-Nonce'] : null;
        $nonceCookie = isset($_COOKIE['wugstracker-nonce']) ? htmlspecialchars($_COOKIE["wugstracker-nonce"]) : null;

        if($nonceHeader === $nonceCookie) {
            $next();            
        } else {
            $res->status(301)->error('Forbidden');
        }
    }

    public static function wp_admin_middleware($req, $res, $next) {
        global $current_user;
        if($current_user->ID != 0 && current_user_can('administrator')) {
            $next();            
        } else {
            $res->status(301)->error('Forbidden'); 
        }
    }

    public static function get_log($req, $res) {
        global $wugsdb;
        
        $params = $req->params;
        $logId = isset($params['logId']) ? $params['logId'] : null;

        if($logId) {
            $result = $wugsdb->logs->findById($logId);
            $res->status(200)->send($result);
        } else {            
            $res->status(400)->error('ID not found');
        }
    }

    public static function update_log($req, $res) {
        global $wugsdb;
        
        $params = $req->params;
        $logId = isset($params['logId']) ? $params['logId'] : null;        
        $dataToUpdate = $req->body;
        $dataToUpdate['updated_at'] = new \DateTime();
        unset($dataToUpdate['_id']);

        if($logId) {
            $exist = $wugsdb->logs->findById($logId);
            if($exist) {
                $result = $wugsdb->logs->updateById($exist['_id'], $dataToUpdate);  
                $res->status(200)->send($result);   
            } else {
                $res->status(400)->error('Log not found');
            }
        } else {            
            $res->status(400)->error('ID not found');
        }
    }

    public static function remove_log($req, $res) {
        global $wugsdb;

        $params = $req->params;
        $logId = isset($params['logId']) ? $params['logId'] : null;

        if($_id) {
            $result = $wugsdb->logs->deleteBy($_id);
            $res->status(200)->send($result);
        } else {            
            $res->status(400)->error('ID not found');
        }
    }

    public static function save_log($req, $res) {
        global $wugsdb;
        
        $doc = $req->body;
        $doc['count'] = 1;
        $doc['browser_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $doc['browser'] = get_browser();
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $doc['ip'] = $ip;
        $doc['updated_at'] = new \DateTime();
        $doc['created_at'] = new \DateTime();

        $exist = $wugsdb->logs->findOneBy(['msg', '=', $doc['msg']]);
        $count = isset($exist['count']) ? ($exist['count']+1) : 1;
        if($exist) {
            $result = $wugsdb->logs->updateById($exist['_id'], [ "count" => $count, "updated_at" => $doc['updated_at'] ]);
            $msg = "Error persistency (times: ".$count.") at " . $doc['updated_at']->format('Y-m-d H:m:i');
            $doc['parent_id'] = $result['_id'];
            $result_child = $wugsdb->logs->insert($doc);      
        } else {
            $result = $wugsdb->logs->insert($doc);
            $msg = "New error registered at " . $doc['created_at']->format('Y-m-d H:m:i');
        }
        
        if($result) {
            $doc_activity = [];
            $doc_activity['log_id'] = $result['_id'];
            $doc_activity['updated_at'] = new \DateTime();
            $doc_activity['created_at'] = new \DateTime();
            $doc_activity['msg'] = $msg;
            self::insert_log_activity($doc_activity);
        } 
        
        $res->status(200)->send($result);
    }

    public static function find_logs($req, $res) {
        global $wugsdb;
        
        $query = $req->query;
        $search = isset($query['search']) ? html_entity_decode($query['search']) : null;
        $where = isset($query['where']) ? json_decode(html_entity_decode(str_replace('\\', '', $query['where'])), true) : null;
        $orderBy = isset($query['orderBy']) ? json_decode(html_entity_decode(str_replace('\\', '', $query['orderBy'])), true) : ['created_at' => 'asc'];
        $limit = isset($query['limit']) ? intval($query['limit']) : 100;
        $skip = isset($query['skip']) ? intval($query['skip']) : 0;

            $logsQueryBuilder = $wugsdb->logs->createQueryBuilder();
            if($where) {
                $logsQueryBuilder->where($where);
            }
            if($search) {
                $logsQueryBuilder->search(["msg", "url", "browser_agent", "ip", "created_at.date",  "updated_at.date"], $search);
                $logsQueryBuilder->except(["searchScore"]);
            }
            $logsQueryBuilder
            ->limit($limit)
            ->skip($skip)
            ->orderBy($orderBy)
            ->join(function($log) use ($wugsdb){
                return $wugsdb->logs->findBy([ "parent_id", "=", $log["_id"] ], ['created_at' => 'desc']);
            }, "childs")
            ->join(function($log) use ($wugsdb) {
                return $wugsdb->logs_messages->findBy([ "log_id", "=", $log["_id"] ], ['created_at' => 'desc']);
            }, "messages")
            ->join(function($log) use ($wugsdb) {
                return $wugsdb->logs_activity->findBy([ "log_id", "=", $log["_id"] ], ['created_at' => 'desc']);
            }, "activity");
            
            $results = $logsQueryBuilder->getQuery()->fetch();

        $res->status(200)->send([
            "results" => $results,
            "total" => $wugsdb->logs->count(),
            "total_results" => count($results),
            "orderBy" => $orderBy,
            "skip" => $skip,
            "limit" => $limit
        ]);
    }

    public static function logs_count($req, $res) {
        global $wugsdb;

        $result = $wugsdb->logs->count();
        $res->status(200)->send($result);
    }

    public static function find_log_activity($req, $res) {
        global $wugsdb;

        $query = $req->query;

        $log_id = isset($query['log_id']) ? $query['log_id'] : null;
        $orderBy = isset($query['orderBy']) ? (array) json_decode(str_replace('\\', '', $query['orderBy']), true) : [];
        $limit = isset($query['limit']) ? intval($query['limit']) : 100;

        $results = $wugsdb->logs_activity->findBy(['log_id', '=', $log_id], $orderBy, $limit, $offset);
        $res->status(200)->send($results);
    }

    public static function insert_log_activity($doc) {
        global $wugsdb;

        $doc['updated_at'] = new \DateTime();
        $doc['created_at'] = new \DateTime();
        $result = $wugsdb->logs_activity->insert($doc);
        return $result;
    }

    public static function find_log_messages($req, $res) {
        global $wugsdb;

        $query = $req->query;

        $log_id = isset($query['log_id']) ? $query['log_id'] : null;
        $orderBy = isset($query['orderBy']) ? (array) json_decode(str_replace('\\', '', $query['orderBy']), true) : [];
        $limit = isset($query['limit']) ? intval($query['limit']) : 100;
        $offset = isset($query['offset']) ? intval($query['offset']) : 0;

        $results = $wugsdb->logs_messages->findBy(['log_id', '=', $log_id], $orderBy, $limit, $offset);
        $res->status(200)->send($results);
    }

    public static function save_log_message($req, $res) {
        global $wugsdb;

        $doc = $req->body;
        $doc['updated_at'] = new \DateTime();
        $doc['created_at'] = new \DateTime();
        $result = $wugsdb->logs_messages->insert($doc);
        
        $res->status(200)->send($result);
    }

    public static function update_log_message($req, $res) {
        global $wugsdb;
        
        $doc = $req->body;
        $_id = isset($body['_id']) ? $body['_id'] : null;
        unset($doc['_id']);
        $doc['updated_at'] = new \DateTime();

        $exist = $wugsdb->logs_messages->findOneBy(['_id', '=', $_id]);
        if($exist) {            
            $result = $wugsdb->logs_messages->updateById($_id, $doc);
        } else {
            $result = $wugsdb->logs_messages->insert($doc);
        }
        
        $res->status(200)->send($result);
    }

    public static function remove_log_message($req, $res) {
        global $wugsdb;
        
        $body = $req->body;
        $_id = isset($body['_id']) ? $body['_id'] : null;

        if($_id) {
            $result = $wugsdb->logs_messages->deleteBy($_id);
            $res->status(200)->send($result);
        } else {            
            $res->status(400)->error('ID not found');
        }
    }
}
