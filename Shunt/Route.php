<?php

namespace Shunt {

    /**
     * The Route class is a container for information about this route
     */
    class Route extends Base {
        
        // the name of our route
        public $name = null;
        // store our actual route expression for convenience
        public $regex = null;
        // the expression as defined when the route was creates
        public $expr = null;
        // SSL route?
        public $https = false;
        // allow pass through for non-matching params?
        public $skip = false;
        // types of authentication to be used if any
        public $auth = false;
        public $allowedMethods = array(
            \Shunt::POST,
            \Shunt::GET,
            \Shunt::PUT,
            \Shunt::DEL
        );
        public $allowedModes = array(
            \Shunt::CLI,
            \Shunt::HTTP,
            \Shunt::AJAX
        );
        public $params = null; // specified params
        public $extraParams = null; // extra params specified by the greedy token
        public $validate = null; // validation rules in the form of regex snippets for params in a route
        public $required_params = null; // parameters required by the callback
        public $defaultArgs = array(); // default values for optional params
        public $callback = null;

        public function __construct($name) {
            parent::__construct();
//            switch ($this->config['snug.https']) {
//                case \Snug::HTTPS_SWITCH:
//                    $this->https = false;
//                    break;
//
//                case \Snug::HTTPS_ALWAYS:
//                    $this->https = true;
//                    break;
//
//                case \Snug::HTTPS_NEVER:
//                    $this->https = false;
//                    break;
//            }
            $this->name = $name;
            $this->routes[$name] = $this;
            return $this;
        }

        public function callback($callback) {
            $this->callback = $callback;
            return $this;
        }

        public function url($route) {
            $self = $this;
            
            //Convert URL params into regex patterns, construct a regex for this route, init params
            $regex = preg_replace_callback('`\$([\w]+)\+?`', function($match) use($self) {
                        $self->params[] = $match[1];

                        if (isset($self->validate[$match[1]])) {
                            return '(?P<' . $match[1] . '>' . $self->validate[$match[1]] . ')';
                        }
                        if (substr($match[0], -1) == '+') {
                            $self->extraParams[$match[1]] = true;
                            return '(?P<' . $match[1] . '>.+)';
                        }
                        return '(?P<' . $match[1] . '>[^/]+)';
                    }, str_replace(array('*', ')', '.'), array('+', ')?', '\\.'), (string) $route));
            if (substr($route, -1) === '/') {
                $regex .= '?';
            }
            $this->regex[] = $regex;
            $this->expr[] = $route;
            return $this;
        }
        
        /**
         * Allows us to set defaults for our params by passing in an associative array
         * @param type $default_params
         */
        public function defaults($default_params = array()) {
            $this->defaultArgs = array_merge($this->defaultArgs, $default_params);
            return $this;
        }
        
        public function test($route, \Shunt\Request $lookup) {
            $params = array();
            //Cache URL params' names and values if this route matches the current HTTP request
            if (!preg_match('`^' . $this->regex[0] . '$`i', $route, $match)) {
                return null;
            }
            //$this->using('debug')->pre($lookup);
            foreach ($this->params as $name) {
                if (isset($match[$name])) {
                    if (isset($this->extraParams[$name])) {
                        $params[$name] = explode('/', urldecode($match[$name]));
                    } else {
                        $params[$name] = urldecode($match[$name]);
                    }
                } 
            }

            $lookup->url = $route;
            $lookup->args = $params;
            $lookup->route = $this;
            
            //$this->using('debug')->pre($lookup);

            // check whether the allowed methods are acceptable
            if (in_array($this->getMethod(), $this->allowedMethods) &&
                    in_array($this->getMode(), $this->allowedModes)) {
                // successfully matched
                return $lookup;
            } else if (!$this->skip) {
                // return a 405 Method not allowed
                throw new \Shunt\Error\MethodNotAllowed(null, $lookup);
            }
            return false;
        }

        /* end** */

        // must this route use https - false turns of https
//        public function https($on = true) {
//            $config = \Snug::Appx()->Config;
//            if ($on) {
//                switch ($config['snug.https']) {
//                    case \Snug::HTTPS_SWITCH:
//                        $this->https = true;
//                        break;
//
//                    case \Snug::HTTPS_ALWAYS:
//                        $this->https = true;
//                        break;
//
//                    case \Snug::HTTPS_NEVER:
//                        $this->https = false;
//                        break;
//                }
//            } else {
//                switch ($config['snug.https']) {
//                    case \Snug::HTTPS_SWITCH:
//                        $this->https = false;
//                        break;
//
//                    case \Snug::HTTPS_ALWAYS:
//                        $this->https = true;
//                        break;
//
//                    case \Snug::HTTPS_NEVER:
//                        $this->https = false;
//                        break;
//                }
//            }
//            return $this;
//        }
//
//        /**
//         * The route cannot be accessed unless verified and authenticated
//         */
//        public function auth() {
//            $args = func_get_args();
//            if (!$args) {
//                // empty means clear ALL auth bindings
//                $this->auth = false;
//            } else {
//                $this->auth = $args;
//            }
//            return $this;
//        }
//
        // if this route is found, but doesn't match the method or mode, allow it to try find the next matching route
        public function skip($skip = true) {
            $this->skip = (bool) $skip;
            return $this;
        }
//
//        // allows us to "rename" route namees - useful for building urls via the name
//        public function rename($name) {
//            $app = \Snug::Appx();
//            unset($app->Routes[$this->name]);
//
//            $this->name = $name;
//            $app->Routes[$name] = $this;
//            return $this;
//        }
//
//        /* validates a param arg matching a regex snippet */
//
//        public function valid(array $rules) {
//            $this->validate = array_merge($this->validate, $rules);
//            return $this;
//        }
//
        /**
         * restricts the route to certain methods, ie. post, get, delete, put
         */
        public function methods() {
            $args = func_get_args();
            $methods = array();
            if ($args) {
                foreach ($args as $arg) {
                    switch (strtolower(trim($arg))) {
                        case \Shunt::POST:
                        case \Shunt::GET:
                        case \Shunt::PUT:
                        case \Shunt::DEL:
                        case \Shunt::OPT:
                        case \Shunt::HEAD:
                            $methods[] = $arg;
                            break;

                        default:
                        // TODO: throw an exception here
                    }
                }
            }
            $this->allowedMethods = $methods;
            unset($methods, $args, $arg);
            return $this;
        }

        /**
         * restricts the route to certain modes, ie. cli, http and/or ajax
         */
        public function modes() {
            $args = func_get_args();
            $modes = array();
            if ($args) {
                foreach ($args as $arg) {
                    switch (strtolower(trim($arg))) {
                        case \Shunt::CLI:
                        case \Shunt::AJAX:
                        case \Shunt::HTTP:
                            $modes[] = $arg;
                            break;

                        default:
                        // TODO: throw an exception here
                    }
                }
            }
            $this->allowedModes = $modes;
            unset($modes, $args, $arg);
            return $this;
        }

    }

}
