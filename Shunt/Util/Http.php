<?php

namespace Shunt\Util {

    class Http {

        // general version for all accept types
        // HTTP ACCEPTS headers differ greatly across the various browsers. At the end of the day, it cannot be trusted
        // to determine the type of content to spit out to the requestor. This has been noted frequently in various articles and posts
        // around the interwebs.
        // Solution? User agents need to explicitly request a specific content type via HTTP ACCEPTS
        // Browsers etc will set several options, and their associated q scores. So we look for those, and if found, don't allow
        // HTTP ACCEPTS to be negotiated.

        public function getAccepts($input, $default) {
            // determine whether the value has quantifiers
            if (strpos($input, ';q=') === false) {
                // we're cool, it is an explicit accepts
                //determine whether it's a valid content type
                if ($this->getContentType($input)) {
                    return trim($input);
                }
            }
            return $default; // we want to use our own default
        }

        // gets the type of http status - pass in the code
        public function getStatusType($status_code) {
            // according to restpatterns.org, the first digit of a status code indicates its description
            $type = substr($status_code, 0, 1);
            switch ($type) {
                case 1:
                    return 'information';
                    break;

                case 2:
                    return 'success';
                    break;

                case 3:
                    return 'supplemental';
                    break;

                case 4:
                case 5:
                    return 'error';
                    break;

                default:
                    return 'unknown';
            }
        }

        // gets the http status codes - pass in the code to get the message
        public function getStatusMessage($status_code) {

            $codes = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable', // use when the site is down for maintenance
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
            );

            return (isset($codes[(int) $status_code])) ? $codes[(int) $status_code] : false;
        }
        
        /*
         * Sets the http status header
         */
        public function setStatusHeader($status_code) {
            // set our status
            $status_header = 'HTTP/1.1 ' . $status_code . ' ' . $this->getStatusMessage($status_code);
            // set the status
            header($status_header);
        }

    }

}
