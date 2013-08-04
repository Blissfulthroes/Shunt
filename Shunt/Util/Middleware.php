<?php

namespace Shunt\Util {

    defined('SHUNT_ACCESS_SECURED') or header('location: /403-Forbidden');

    class Middleware {
        
        public $definitions = array();

        public function define($name, \Closure $callback) {
            $this->definitions[$name] = $callback;
        }
        
        public function call() {
            $args = func_get_args();
            $name = array_shift($args);
            if(isset($this->definitions[$name]) && is_callable($this->definitions[$name])) {
                return call_user_func_array($this->definitions[$name], $args);
            }
        }
        
        public function destroy($name) {
            if(isset($this->definitions[$name])) {
                unset($this->definitions[$name]);
            }
        }

    }

}