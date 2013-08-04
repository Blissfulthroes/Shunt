<?php

namespace Shunt {

    class Registry {

        private static $instance = null;
        private $loaders = array();
        private $utils = array();
        private $globals = array();

        /**
         * 
         * @return type
         */
        public static function getInstance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * 
         * @param type $name
         * @return type
         */
        public function __isset($name) {
            return isset($this->globals[$name]);
        }
        
        /**
         * 
         * @param type $name
         * @return type
         */
        public function __get($name) {
            if(isset($this->globals[$name])) {
                return $this->globals[$name];
            }
        }
        
        /**
         * 
         * @param type $name
         * @param type $value
         * @return type
         */
        public function __set($name, $value) {
            return $this->globals[$name] = $value;
        }
        
        /**
         * 
         * @param type $name
         * @return null
         */
        public function get($name = null) {
            if(is_null($name)) {
                return $this->globals;
            } else if(isset ($this->globals[$name])) {
                return $this->globals[$name];
            } else {
                return null;
            }
        }
        
        /**
         * 
         * @param type $name
         * @param type $value
         * @return type
         */
        public function set($name, $value) {
            return $this->globals[$name] = $value;
        }

        /**
         * Register new libs with the framework autoloader
         * @param type $namespace
         * @param type $path
         */
        public function setLoader($namespace, $path) {
            $namespace = $this->normalizeNamespace($namespace);
            $this->loaders[$namespace] = new \Shunt\Loader($namespace, $path);
            unset($namespace, $path);
            return true;
        }

        /**
         * 
         * @param type $namespace
         * @return null
         */
        public function getLoader($namespace) {
            $namespace = $this->normalizeNamespace($namespace);
            if (isset($this->loaders[$namespace])) {
                return $this->loaders[$namespace];
            } else {
                return null;
            }
        }
        
        public function getLoaders() {
            return $this->loaders;
        }

        /**
         * 
         * @param type $old_namespace
         * @param type $new_namespace
         * @param type $path
         * @throws Exception
         */
        public function replaceLoader($old_namespace, $new_namespace, $path = false) {
            if (!$path) {
                if (!is_null($this->getLoader($old_namespace))) {
                    $path = $this->getLoader($old_namespace)->path;
                } else {
                    throw new Exception('$path must be supplied');
                }
            }
            $this->removeLoader($old_namespace);
            $this->setLoader($new_namespace, $path);
        }

        /**
         * 
         * @param type $namespace
         */
        public function removeLoader($namespace) {
            $this->getLoader($namespace)->unregister();
            unset($this->loaders[$namespace]);
        }

        /**
         * 
         * @param type $util
         * @return null
         */
        public function getUtil($util) {
            if (isset($this->utils[$util])) {
                return $this->utils[$util];
            } else {
                // look in our App folder for the util
                $utilName =  '\\App\Util\\' . $util;
                if (class_exists($utilName)) {
                    $this->utils[$util] = new $utilName();
                    return $this->utils[$util];
                } else {
                    // look in our Shunt folder for the util
                    $utilName =  '\\Shunt\Util\\' . $util;
                    if (class_exists($utilName)) {
                        $this->utils[$util] = new $utilName();
                        return $this->utils[$util];
                    }
                }
            }
            return null;
        }

        /**
         * 
         * @param type $namespace
         * @return type
         */
        private function normalizeNamespace($namespace) {
            $namespace = ltrim($namespace, '\\');
            $namespace = rtrim($namespace, '\\');
            return $namespace;
        }

    }

}
