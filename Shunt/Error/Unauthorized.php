<?php

namespace Shunt\Error {

    class Unauthorized extends \Shunt\Error {

        public $status_code = 401;
        public $title = 'Unauthorized';
        public $default_message = 'The Application could not verify that you are authorized to access the resource you requested.';

    }
}
