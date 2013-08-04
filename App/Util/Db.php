<?php

namespace App\Util {

    defined('SHUNT_ACCESS_SECURED') or header('location: /403-Forbidden');

    class Db {
        
        public $callback = null;

        public function setup(\Closure $callback) {
            $this->callback = $callback;
            //return call_user_func_array($callback, array());
        }
        
        public function call() {
            $args = func_get_args();
            if(is_callable($this->callback)) {
                return call_user_func_array($this->callback, $args);
            }
        }

    }

}