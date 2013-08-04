<?php

namespace Shunt {

    class Env {

        private static $instance = null;

        const POST = 'post';
        const PUT = 'put';
        const GET = 'get';
        const DEL = 'delete';
        const OPT = 'options';
        const HEAD = 'head';
        const CLI = 'cli';
        const HTTP = 'http';
        const AJAX = 'ajax';
        const CUSTOM_HEADERS = 'SHUNT_';
        const BASIC = 'basic';
        const DIGEST = 'digest';
        const SIGNED = 'signed'; // preferred method of authenticating via a RESTful service
        const AUTH_SCHEMA = 'SHUNT'; // used for http authentication
        const HTTPS_NEVER = 'never'; // never switch https
        const HTTPS_SWITCH = 'switch'; // honour https switching
        const HTTPS_ALWAYS = 'always'; // always use https
        const SECURED = "DEFINED('SHUNT_SECURED') or header('location: 404');";

        private $domain = null;
        private $serverName = null;
        private $port = null;
        private $ip = null;
        private $url = null;
        private $queryString = null;
        private $queryStringData = null;
        private $https = 'off';
        private $mode = 'http';
        private $method = 'get';
        private $contentType = 'text/html';
        private $acceptContentType = null;
        private $acceptCharset = null;
        private $acceptLanguage = null;
        private $acceptEncoding = null;
        private $customHeaders = array();
        private $browser = null;
        private $apc = false;
        private $rawInput = null;

        /**
         * 
         */
        public function __construct($new = null) {
            if (is_null($new)) {
                // build our data
                $env = array();
                
                //echo '<pre>' . print_r($_SERVER, 1) . '</pre>';
                // are we using APC?
                $env['apc'] = extension_loaded('apc');

                if (isset($_SERVER['SERVER_NAME'])) {
                    $env['domain'] = $_SERVER['SERVER_NAME'];
                    // the first is always without the www
                    $env['serverName'] = str_replace('www.', '', $_SERVER['SERVER_NAME']);
                }

                if (isset($_SERVER['SERVER_PORT'])) {
                    $env['port'] = $_SERVER['SERVER_PORT'];
                }
                // from Stackoverflow
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                    $env['ip'] = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                    $env['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $env['ip'] = $_SERVER['REMOTE_ADDR'];
                }
                $env['urlStub'] = SHUNT_URL_STUB; // used for subdirectory routing
                $env['docRoot'] = SHUNT_DOCROOT;
                $env['shunt'] = SHUNT;

                // look for custom headers used by Shunt
                $custom_headers = array();
                foreach ($_SERVER as $k => $v) {
                    // custom headers
                    $pos = stripos($k, '_X_');
                    // does it exist, and is there a value there?
                    if ($pos !== false && $v) {
                        // clean up for Shunt headers ie. [X_][custom]
                        $k = str_replace(self::CUSTOM_HEADERS, '', $k);
                        $custom_headers[strtolower(substr($k, ($pos + strlen(self::CUSTOM_HEADERS))))] = $v;
                    }
                }
                $env['customHeaders'] = ($custom_headers) ? $custom_headers : false;
                unset($k, $v, $custom_headers);

                // is this an https request?
                $env['https'] = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : 'off';
                // get our request method
                $env['method'] = (isset($_SERVER['REQUEST_METHOD'])) ? strtolower($_SERVER['REQUEST_METHOD']) : self::GET;
                // if our request method comes through as none, use get as the default
                if ($env['method'] == 'none') {
                    $env['method'] = self::GET;
                }
                // what type of data is the request expecting as a response - empty values will default to those used by Chrome
                //$env['acceptContentType'] = (isset($_SERVER['HTTP_ACCEPT'])) ? \Shunt\Http::accepts($_SERVER['HTTP_ACCEPT'], 'text/html') : 'text/html';
                //$env['acceptCharset'] = (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) ? \Shunt\Http::accepts($_SERVER['HTTP_ACCEPT_CHARSET'], 'utf-8') : 'utf-8';
                //$env['acceptLanguage'] = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? \Shunt\Http::accepts($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en') : 'en';
                $env['acceptEncoding'] = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : false;

                // what format is the request
                if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE']) {
                    $env['contentType'] = $_SERVER['CONTENT_TYPE'];
                } else if (isset($env['customHeaders']['content_type']) && $env['customHeaders']['content_type']) {
                    // use our custom fall back
                    $env['contentType'] = $env['customHeaders']['content_type'];
                } else {
                    // last chance
                    $env['contentType'] = 'text/html';
                }

                // we also want to know whether this was via http, cli or ajax
                $sapi = php_sapi_name();
                if (in_array($sapi, array('cli', 'cgi')) && empty($_SERVER['REMOTE_ADDR'])) {
                    $env['mode'] = self::CLI;
                    $env['browser'] = 'none';
                    // our accept content type needs to change
                    $env['acceptContentType'] = 'text/plain';
                } else if (isset($env['customHeaders']['requested_with']) && $env['customHeaders']['requested_with'] == 'XMLHttpRequest') {
                    $env['mode'] = self::AJAX;
                    $env['browser'] = new \Shunt\Browser(self::getUABrowser());
                } else {
                    $env['mode'] = self::HTTP;
                    $env['browser'] = new \Shunt\Browser(self::getUABrowser());
                }
                // we now need to determine the data that came in 
                switch ($env['mode']) {
                    case self::CLI:
                        // Shunt Command Interface or SCI

                        $sn = false;
                        $p = false;
                        $r = false;
                        // is the first equal to index.php?
                        if (basename($_SERVER['argv'][0]) == 'shunt') {

                            echo $_SERVER['argv'][0];
                            // remove it
                            array_shift($_SERVER['argv']);

                            print_r($_SERVER['argv']);

                            // argv[0] is always the task ([serverName]:[task])
                            if (isset($_SERVER['argv'][0])) {
                                // check whether is is a command, or just a server name
                                if (stripos($_SERVER['argv'][0], ':') !== false) {
                                    $split = explode(':', $_SERVER['argv'][0]);
                                    $_SERVER['SERVER_NAME'] = trim($split[0]);
                                    $task = trim($split[1]);
                                } else {
                                    $_SERVER['SERVER_NAME'] = trim($_SERVER['argv'][0]);
                                    $task = 'options';
                                }
                                array_shift($_SERVER['argv']);
                            }

                            // set up the server name we are spoofing for CLI
                            $env['serverName'][] = str_replace('www.', '', $_SERVER['SERVER_NAME']);
                            $env['serverName'][] = $_SERVER['SERVER_NAME'];

                            echo 'Server : ' . $env['serverName'][0] . "\n";
                            echo 'Task : ' . $task . "\n";

                            // next, we want to take out any options (prefix is -- and are named pairs eg. --name="Fred")
                            $options = array();
                            $arguments = array();

                            foreach ($_SERVER['argv'] as $paramsk => $paramsv) {
                                if (substr($paramsv, 0, 2) == '--') {
                                    $opt = explode('=', $paramsv);
                                    $options[substr(trim($opt[0]), 2)] = $opt[1];
                                } else {
                                    $arguments[] = $paramsv;
                                }
                                unset($_SERVER['argv'][$paramsk]);
                            }
//                            
//                                
//                                $split = explode(':', $params);
//                                switch ($split[0]) {
//                                    case '-sn':
//                                        $sn = $split[1];
//                                        break;
//
//                                    case '-r':
//                                        $r = $split[1];
//                                        break;
//
//                                    default:
//                                        $p[$split[0]] = $split[1];
//                                        break;
//                                }
//                            }
                            print_r($options);
                            print_r($arguments);

                            if ($sn && $r) {
                                // everything looks right, so map 'em
                                // fake the server name
                                $_SERVER['SERVER_NAME'] = $sn;
                                $env['serverName'] = $sn;
                                $env['queryString'] = '';
                                $env['url'] = $r;
                            } else {
                                die("For Shunt Command Interface options, please provide the server name being queried.\neg. php shunt [serverName] or php shunt [serverName]:options");
                            }
                        }
                        $env['rawInput'] = '';
                        // some gc
                        unset($sn, $p, $r);
                        break;

                    // all other requests go through default
                    default:
                        // to get out uri, we need to do some gymnastics
                        $env['url'] = '/';
                        $env['queryString'] = $_SERVER['QUERY_STRING'];
                        $env['queryStringData'] = array();
                        $splitter = explode('&', urldecode($env['queryString']));
                        if ($splitter) {
                            foreach ($splitter as $split) {
                                $parts = explode('=', $split);
                                if ($split) {
                                    $env['queryStringData'][$parts[0]] = $parts[1];
                                }
                            }
                        }
                        unset($split, $splitter, $parts);

                        if (isset($_SERVER['REDIRECT_URL'])) {
                            $env['url'] = $_SERVER['REDIRECT_URL'];
                        } else if (isset($_SERVER['REQUEST_URI'])) {
                            $env['url'] = str_replace('?' . $env['queryString'], '', $_SERVER['REQUEST_URI']);
                        }
                        // check for our stub
                        if ($env['urlStub']) {
                            $env['url'] = substr($env['url'], strlen($env['urlStub']));
                        }
                        $env['rawInput'] = trim(file_get_contents('php://input'));
                }
            }
            if ($new) {
                $env = array_merge($env, $new);
            }

            foreach ($env as $k => $v) {
                $this->$k = $v;
            }
            unset($env);
        }

        // allows us to load environment variables for unit testing
        /**
         * 
         * @param type $env
         * @return type
         */
        public static function getInstance($env = null) {
            if (!is_null($env)) {
                self::$instance = new self($env);
            }
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function inQueryStringData($index) {
            if(isset($this->queryStringData[$index])) {
                return $this->queryStringData[$index];
            }
            return null;
        }

        /**
         * 
         * @param type $index
         * @return null
         */
        public function getEnviromentalVariable($index) {
            if (!is_null($index)) {
                if (isset($this->$index)) {
                    return $this->$index;
                } else {
                    return null;
                }
            }
            return null;
        }
        
        /**
         * 
         * @param type $index
         * @param type $val
         * @return type
         */
        public function setEnviromentalVariable($index, $val) {
            $this->$index = $val;
            return $this->$index;
        }

        /**
         * 
         * @return string
         */
        public static function getUABrowser() {
            // determine the info first
            $useragent = $_SERVER['HTTP_USER_AGENT'];
            $browser = array();

            if (strchr($useragent, "MSIE")) {
                $browser['browser'] = 'IE';
                preg_match('|MSIE ([0-9]\.[0-9]); |', $useragent, $match);
                $browser['fullVersion'] = $match[1];
            } else if (strchr($useragent, "Firefox")) {
                $browser['browser'] = 'Firefox';
                preg_match('|Firefox/(.*)|', $useragent, $match);
                $browser['fullVersion'] = $match[1];
            } else if (strchr($useragent, "Opera")) {
                $browser['browser'] = 'Opera';
                preg_match('|Opera/(.*) \(|', $useragent, $match);
                $browser['fullVersion'] = $match[1];
            } else if (strchr($useragent, "Chrome")) {
                $browser['browser'] = 'Chrome';
                preg_match('|Chrome/(.*) |', $useragent, $match);
                $browser['fullVersion'] = $match[1];
            } else if (strchr($useragent, "Safari")) {
                $browser['browser'] = 'Safari';
                preg_match('|Version/(.*) |', $useragent, $match);
                $browser['fullVersion'] = $match[1];
            } else {
                $browser['browser'] = 'Unknown';
                $browser['fullVersion'] = '1.0';
            }
            unset($match, $useragent);
            $return = $browser['browser'] . ' ' . $browser['fullVersion'];
            unset($browser);
            return $return;
        }

    }

}