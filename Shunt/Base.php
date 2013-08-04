<?php

namespace Shunt {

    class Base {

        protected $registry = null;
        protected $env = null;
        protected $routes = null;

        /**
         * 
         */
        public function __construct() {
            $this->registry = \Shunt\Registry::getInstance();
            $this->env = \Shunt\Env::getInstance();
        }

//        public function __isset($name) {
//            return isset($this->registry->$name);
//        }
//
//        public function __get($name) {
//            return $this->registry->get($name);
//        }
//
//        public function __set($name, $value) {
//            return $this->registry->set($name, $value);
//        }

        /**
         * There is no set method for env variables, only a getter
         * @param type $method
         * @param type $params
         * @return type
         */
        public function __call($method, $params) {
            if (strtolower(substr($method, 0, 3)) == 'get') {
                $var = lcfirst(substr($method, 3));
                return $this->env->getEnviromentalVariable($var);
            } else if(strtolower(substr($method, 0, 3)) == 'set') {
                $var = lcfirst(substr($method, 3));
                return $this->env->setEnviromentalVariable($var, $params[0]);
            }
        }

        /**
         * 
         * @param type $name
         * @return type
         */
        public function get($name) {
            return $this->registry->get($name);
        }

        /**
         * 
         * @param type $name
         * @param type $value
         * @return type
         */
        public function set($name, $value) {
            return $this->registry->set($name, $value);
        }

        // references a namespace with a path (ie. sets up a Loader)
        /**
         * 
         * @return type
         */
        public function load() {
            // count the number of args passed to determine the action
            $num_args = func_num_args();

            switch ($num_args) {
                case 0:
                    return $this->registry->getLoaders();
                    break;

                case 1:
                    return $this->registry->getLoader(func_get_arg(0));
                    break;

                case 2:
                    return $this->registry->setLoader(func_get_arg(0), func_get_arg(1));
                    break;

                case 3:
                    return $this->registry->replaceLoader(func_get_arg(0), func_get_arg(1), func_get_arg(2));
                    break;
            }
        }
        
        /**
         * 
         * @param type $remove
         * @return type
         */
        public function unload($remove) {
            return $this->registry->removeLoader($remove);
        }

        public function using($util) {
            // utils are always short lowercased words, the first letter is uppercased to get the actual classname
            // eg. $this->util('debug') refers to Shunt\Utils\Debug
            return $this->registry->getUtil(ucfirst($util));
        }
        
        /** Determines whether a key exists in the querystring, and its value
         * 
         * @param type $key
         */
        public function queryUrl($key) {
            return $this->env->inQueryStringData($key);
        }

        /**
         * header redirect or response depending on the desired effect
         * NOTE: redirecting to another page will always result in a 302 Redirect
         * @param type $url
         * @param type $status
         */
        public function respond($target) {
            ob_start();
            if (is_int($target) && strlen($target) == 3) {
                // its a status code
                $this->util('http')->setStatusHeader($target);
            } else {
                header('Location: ' . $target);
            }
            ob_clean();
            exit;
        }

        // routing is built into the app - returns the route object
        // all routes have to have an alias, which is the first argument, to make use it is defined
        public function route($alias) {
            //$this->route('GET|POST HTTP|CLI SKIP SSL /test/$arg(/$wtf)', function($arg, $wtf="hello"){});
            if (isset($this->routes[$alias])) {
                return $this->routes[$alias];
            } else {
                return $this->routes[$alias] = new Route($alias);
            }
        }

        public function getRoutes() {
            return $this->routes;
        }

        /**
         * Lookup a matching route
         * @param type $expr
         * @return type Either an instance of Request if successful, or an error instance if not
         */
        public function find($expr) {
            //$snug = self::Appx();
            try {
                // create a new instance of Request, but hold it in Lookup
                $lookup = new \Shunt\Request();

                // first check whether we can match any named routes
                if ($this->routes) {
                    foreach ($this->routes as $alias => $route) {
                        $ret = $route->test($expr, $lookup);
                        if ($ret) {
                            //echo '<pre>' . print_r($ret, 1) . '</pre>';
                            
                            // merge any params with defaults
                            $params = array_merge($ret->route->paramDefaults, $ret->params);
                            //echo '<pre>' . print_r($params, 1) . '</pre>';
                            return $lookup;
                        }
                    }
                }
                unset($alias, $match, $expr, $route, $k, $v, $return);
                throw new \Shunt\Error\NotFound(null, $lookup);
            } catch (\Shunt\Error $e) {
                return $e;
            }
        }
        
        // allows us to build a url
        public function buildUrl($name, $params = array()) {
            if(isset($this->routes[$name])) {
                // we want the expression
                $expr = str_replace(array('(', ')', '*'), null, $this->routes[$name]->expr[0]);
                $extra = false;
                
                // merge in our defaults
                $params = array_merge($this->routes[$name]->paramDefaults, $params);
                foreach($params as $k => $v) {
                    $var = '$' . $k;
                    if(stripos($expr, $var) !== false) {
                        if(is_array($v)) {
                            $v = implode('/', $v);
                        }
                        $expr = str_replace($var, $v, $expr);
                        unset($params[$k]);
                    } else {
                        $extra[] = $k . '=' . urlencode($v);
                    }
                }
                // if there are any params left over, add them as part of the querystring
                if($extra) {
                    $expr .= '?' . implode('&', $extra);
                }
                $this->using('debug')->pre($expr);
                // check whether some of the params have not been replaced
                if(stripos($expr, '$') === false) {
                    return $expr;
                }
            }
            return false;
        }

    }

}
