<?php

namespace Shunt\Error {

    class BadRequest extends \Shunt\Error {

        public $status_code = 400;
        public $title = 'Bad Request';
        public $defaultMessage = 'Your browser (or proxy) sent a request that the Application could not understand.';

    }
}
