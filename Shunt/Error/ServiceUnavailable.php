<?php

namespace Shunt\Error {

    class ServiceUnavailable extends \Shunt\Error {

        public $status_code = 503;
        public $title = 'Service Unavailable';
        public $defaultMessage = 'The Application is currently offline. Please try again later.';

    }
}
