<?php

namespace Shunt\Error {

    /** TODO: add the full url requested to env */
    class NotFound extends \Shunt\Error {

        public $status_code = 404;
        public $title = 'Not Found';
        public $defaultMessage = 'Sorry, but the resource you were looking for either could not be found, or does not exist.';
        
        public function __construct($message = null, \Shunt\Request $request = null, $code = 0, \Shunt\Exception $previous = null) {
            $this->request = $request;
            if (is_null($message)) {
                $this->message = $this->defaultMessage;
                $this->messageData = 'Sorry, but the resource located at ' . $this->request->getUrl() . ' either could not be found, or does not exist.';
            }
            parent::__construct($this->message, $request, $code, $previous);
        }

    }

}
