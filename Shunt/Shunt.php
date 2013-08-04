<?php

// we need to load our autoloader first and foremost
require('Loader.php');
require('Registry.php');

/**
 * Facade class which holds our Loader instances, and handles our setup
 */
class Shunt {
    
    const SIGNATURE = 'Shunt PHP Micro Framework';
    const VERSION = '0.1.0';

    const POST = 'post';
    const PUT = 'put';
    const GET = 'get';
    const DEL = 'delete';
    const OPT = 'options';
    const HEAD = 'head';
    const CLI = 'cli';
    const HTTP = 'http';
    const AJAX = 'ajax';
    const CUSTOM_HEADERS = 'SHUNT_';
    const BASIC = 'basic';
    const DIGEST = 'digest';
    const SIGNED = 'signed'; // preferred method of authenticating via a RESTful service
    const AUTH_SCHEMA = 'SHUNT'; // used for http authentication
    const HTTPS_NEVER = 'never'; // never switch https
    const HTTPS_SWITCH = 'switch'; // honour https switching
    const HTTPS_ALWAYS = 'always'; // always use https
    const SECURED = "DEFINED('SHUNT_SECURED') or header('location: 404');";

    /**
     * Sets everything up - never needs to be called directly
     */
    public static function start() {
        // start our output buffer for the sake of headers
        ob_start();
        // define our directory separator
        define('SHUNT_DS', DIRECTORY_SEPARATOR);
        // define our secured constant
        define('SHUNT_ACCESS_SECURED', 1);
        // automatically set error reporting off for safety
        error_reporting(-1);
        //error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', 'On');
        ini_set('log_errors ', 'On');
        // docroot
        // get our true doc root
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $true_doc_root = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $true_doc_root = false;
        }
        $trace = debug_backtrace();
        // will automatically find the doc root, no matter where it lives
        // will be the very last element in our trace
        $doc_root = array_pop($trace);
        $doc_root = dirname($doc_root['file']);
        if (substr($doc_root, 0, 1) !== SHUNT_DS) {
            // make sure we're not talking about a Windows path
            if (!preg_match('/^[a-zA-Z]\:/', $doc_root)) {
                $doc_root = SHUNT_DS . $doc_root;
            }
        }

        define('SHUNT_DOCROOT', $doc_root);
        // zp also always knows where it lives
        define('SHUNT', dirname(__FILE__));
        \Shunt\Registry::getInstance()->setLoader('Shunt', SHUNT);
        // @TODO: these should be configurable
//        \Shunt\Registry::getInstance()->setLoader('Plugins', 'Plugins');
        \Shunt\Registry::getInstance()->setLoader('App', 'App');

        $url_extra = str_replace(array("/", "\\"), SHUNT_DS, $true_doc_root);
        define('SHUNT_URL_STUB', str_replace(array($url_extra, SHUNT_DS), array('', '/'), $doc_root));

        unset($trace, $doc_root, $true_doc_root, $url_extra);
    }
    
    /**
     * Split a string by capitals, replace spaces with underscores and lowercase the string
     * @param type $string
     * @param type $ucfirst
     * @param type $glue
     * @return type
     */
    public static function snakeCase($string, $ucfirst = false, $glue = '_') {
        $pattern = "/(.)([A-Z])/";
        $replacement = "\\1 \\2";
        $return = ($ucfirst) ?
                ucfirst(preg_replace($pattern, $replacement, $string)) :
                strtolower(preg_replace($pattern, $replacement, $string));
        return ($glue) ? str_replace(' ', $glue, $return) : $return;
    }

}

Shunt::start();
// return a new application instance
return new Shunt\App();
