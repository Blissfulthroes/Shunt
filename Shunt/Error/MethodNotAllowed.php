<?php

namespace Shunt\Error {

    class MethodNotAllowed extends \Shunt\Error {

        public $status_code = 405;
        public $title = 'Method Not Allowed';
        public $default_message = 'Method Not Allowed.';
        
        public function __construct($message = null, \Shunt\Request $request = null, $code = 0, \Shunt\Exception $previous = null) {
            $this->request = $request;
            if (is_null($message)) {
                // get allowed methods
                $allowedMethods = $this->request->route->allowedMethods;
                $allow = 'The resource you are trying to access only accepts the following methods: </p><ul>';
                foreach ($allowedMethods as $allowed) {
                    $allow .= '<li>' . strtoupper($allowed) . '</li>';
                }
                $allow .= '</ul>';
                // get allowed methods
                $allowedMethods = $this->request->route->allowedModes;
                $allow .= '<p>In addition, the resource is restricted to the following requests:</p><ul>';
                foreach ($allowedMethods as $allowed) {
                    $allow .= '<li>' . strtoupper($allowed) . '</li>';
                }
                $allow .= '</ul><p>';
                $this->message = $allow;
            }
            parent::__construct($this->message, $request, $code, $previous);
        }

    }
}
