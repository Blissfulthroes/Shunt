<?php

namespace Shunt\Error {

    class MethodNotAllowed extends \Shunt\Error {

        public $status_code = 405;
        public $title = 'Method Not Allowed';
        public $defaultMessage = 'The resource you are trying to access is restricted to certain methods and requests.';
        
        public function __construct($message = null, \Shunt\Request $request = null, $code = 0, \Shunt\Exception $previous = null) {
            $this->request = $request;
            if (is_null($message)) {
                $this->message = $this->defaultMessage;
                // get allowed methods
                $allowedMethods = $this->request->route->allowedMethods;
                $allow = array();
                $allow['methodText'] = 'The resource you are trying to access only accepts the following methods:';
                foreach ($allowedMethods as $allowed) {
                    $allow['methods'][] = strtoupper($allowed);
                }
                // get allowed methods
                $allowedMethods = $this->request->route->allowedModes;
                $allow['modesText'] = 'In addition, the resource is restricted to the following requests:';
                foreach ($allowedMethods as $allowed) {
                    $allow['modes'][] = strtoupper($allowed);
                }
                $this->messageData = $allow;
            }
            parent::__construct($this->message, $request, $code, $previous);
        }

    }
}
