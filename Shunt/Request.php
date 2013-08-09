<?php

namespace Shunt {

    class Request extends Base {

        /**
         * The base url matched during routing (ie. without extras)
         * @var type 
         */
        public $url = null;

        /**
         * Any defined params parsed from the request uri via the router
         * @var type 
         */
        public $args = null;

        /**
         * Points to the route that was matched by the router
         * @var type 
         */
        public $route = null;

        /**
         * The body content, if any, after being processed
         * @var mixed 
         */
        public $data = false;

        /**
         * The constructor.
         * Gets information about the current request.
         * The incoming request is parsed and stored, making information about the request available to us.
         * This includes whether it was an http, ajax or cli initialised request, what content type, what mime-type is the response 
         * expected to be and so forth. 
         * @see sitchRequest::get
         * @return object
         * returns an instance of the sitchRequest class 
         */
        public function __construct() {
            parent::__construct();

            if($this->getRawInput()) {
                $this->data = $this->applyRawInput($this->getContentType(), $this->getRawInput());
            }
        }

        /**
         * Processes the body of the request if any, and converts it to native PHP data.
         * If the request has any body, the wRequst object will try convert it to a native format so that it is eay to work with
         * via PHP. For example, json will be converted to a PHP stdClass object.
         * 
         * @param string $format
         * The mime-type of the body, if we're able to determine it.
         * @param mixed $data
         * The incoming body content to be processed.
         * @return mixed
         * The processed body content in an eaily consumable native format. 
         */
        private function applyRawInput($format, $data) {
            if ($data) {
//                switch (\Snug\Http::getContentType($format)) {
//                    case 'json':
//                        $data = \Snug\decode_json($data);
//                        break;
//
//                    case 'xml':
//                        $data = \Snug\decode_xml($data);
//                        break;
//
//                    case 'form':
//                        $data = $data;
//                        break;
//
//                    default:
//                        // we can only decode xml and json
//                        $data = null;
//                }
            }
            return $data;
        }

    }

}
