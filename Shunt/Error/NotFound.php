<?php

namespace Shunt\Error {

    class NotFound extends \Shunt\Error {

        public $status_code = 404;
        public $title = 'Not Found';
        public $default_message = 'Sorry, but the resource you were looking for either could not be found, or does not exist.';

    }

}
