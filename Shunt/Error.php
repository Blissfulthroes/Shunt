<?php

namespace Shunt {

    class Error extends \Exception {

        public $status_code = 500;
        public $title = 'Server Error';
        public $defaultMessage = '';
        public $messageData = '';
        public $exception = ''; // allows us to determine the class if necessary
        public $request = false;

        // Redefine the exception so message isn't optional
        public function __construct($message = null, \Shunt\Request $request = null, $code = 0, Exception $previous = null) {
            if (is_null($message)) {
                $message = $this->defaultMessage;
                $this->messageData = $message;
            }
            $this->exception = get_class();
            $this->request = $request;
            // make sure everything is assigned properly
            parent::__construct($message, $code, $previous);
        }

    }

}
