<?php

namespace Shunt {

    class App extends Base {
        
        public $echo = null;
        public $data = null;

        public function __construct() {
            parent::__construct();
        }
        
        public function run() {
            ob_start();
            // lets find our route
            //echo '<pre>' . print_r(Snug::Appx(), 1) . '</pre>';
            $match = $this->find($this->getUrl());
            $this->using('debug')->pre($match);
            //exit;
                        
            // at this point, we now have our Request object
            
            
            $this->echo = ob_get_contents();
            $this->data = $out;
            ob_end_clean();
            
            echo $this->echo;
        }

    }

}
