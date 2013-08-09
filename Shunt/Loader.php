<?php

namespace Shunt {

    /**
     * autoloader class
     * Allows use to register namespaces with their own path, which allows us a fair level of flexibility
     * <pre><code>
     * <?php $loader = new \Shunt\Loader('Shunt', '/lib/vendor/Shunt'); ?>
     * </code></pre>
     */
    class Loader {

        private $namespace = null;
        private $path = null;
        private $last_loaded = null;

        public function __construct($namespace, $path) {
            $namespace = ltrim($namespace, '\\');
            $namespace = rtrim($namespace, '\\');
            $this->namespace = $namespace;
            $this->path = $path;
            if(!$this->isFile($path)) {
                spl_autoload_register(array($this, 'register'));
            }
        }

        public function unregister() {
            spl_autoload_unregister(array($this, 'register'));
        }

        // PSR-X Autoloader
        public function register($classname) {
            $classname = ltrim($classname, '\\');

            // PSR-0 says all underscores become directory separators
            $classname = str_replace('_', '\\', $classname);
            $path = (substr($this->path, 0, strlen(SHUNT_DOCROOT)) == SHUNT_DOCROOT) ? $this->path : SHUNT_DOCROOT . SHUNT_DS . $this->path;
            // replace the namespace if found with the path
            $location = str_replace($this->namespace, $path, $classname) . '.php';
            // do a replace on these if they're in the path - also reduce the number of DS to 1 between folders
            $location = preg_replace('{\\' . SHUNT_DS . '+}', SHUNT_DS, str_replace(array("/", "\\"), SHUNT_DS, $location));

            // now we can test whether the file exists
            if (is_file($location)) {
                $this->last_loaded = $location;
                include($location);
                unset($classname, $path, $location);
                return true;
            }
            unset($classname, $path, $location);
            return false;
        }
        
        private function isFile($path) {
            // if path is a direct path to a file (ie, has a .php extension), include it straight away
            $file_ext = strtolower(strrchr($path, '.'));
            if (!$file_ext || $file_ext != '.php') {
                return false;
            }
            $path = (substr($path, 0, strlen(SHUNT_DOCROOT)) == SHUNT_DOCROOT) ? $path : SHUNT_DOCROOT . SHUNT_DS . $path;
            // do a replace on these if they're in the path - also reduce the number of DS to 1 between folders
            $path = preg_replace('{\\' . SHUNT_DS . '+}', SHUNT_DS, str_replace(array("/", "\\"), SHUNT_DS, $path));

            if (is_file($path)) {
                $this->last_loaded = $path;
                include($path);
                unset($path);
                return true;
            }
            return false;
        }

    }

}

