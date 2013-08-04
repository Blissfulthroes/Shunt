<?php

namespace Shunt\Error {

    class Forbidden extends \Shunt\Error {

        public $status_code = 403;
        public $title = 'Forbidden';
        public $default_message = 'You don\'t have permission to access the requested resource. It is either read-protected or direct access is forbidden.';

    }
}
