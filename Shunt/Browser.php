<?php

namespace Shunt {

    defined('SHUNT_ACCESS_SECURED') or header('location: /403-Forbidden');

    class Browser {

        const SAFARI = 'Safari';
        const IE = 'Microsoft Internet Explorer';
        const FF = 'Mozilla Firefox';
        const CHROME = 'Google Chrome';
        const OPERA = 'Opera';

        public $name = null;
        public $version = null;
        private $alias = array(
            'safari' => self::SAFARI,
            'microsoft internet explorer' => self::IE,
            'ms internet explorer' => self::IE,
            'internet explorer' => self::IE,
            'msie' => self::IE,
            'ie' => self::IE,
            'mozilla firefox' => self::FF,
            'firefox' => self::FF,
            'ff' => self::FF,
            'google chrome' => self::CHROME,
            'chrome' => self::CHROME,
            'opera' => self::OPERA
        );

        public function __construct($browser) {
            $split = explode(' ', $browser);
            $this->name = $this->aka(trim($split[0]));
            $this->version = trim($split[1]);
            unset($browser, $split);
        }

        public function __toString() {
            return $this->name . ' ' . $this->version;
        }

        public function aka($browser_name) {
            if (isset($this->alias[strtolower($browser_name)]) && $this->alias[strtolower($browser_name)]) {
                return $this->alias[strtolower($browser_name)];
            }
            return 'Unknown';
        }

        private function getIndex($version, $decimals) {
            // we need to generate a version comparison number
            $ver = explode('.', $version);
            $calc = array();
            // each gets padded with leading 0
            for ($i = 0; $i <= $decimals; $i++) {
                $calc[] = str_pad($ver[$i], 8, 0, STR_PAD_LEFT);
            }
            $index = (count($calc) > 1) ? implode('', $calc) : $calc[0];
            return $index;
        }

        public function is($browser_name) {
            if ($this->aka($browser_name) == $this->name) {
                return true;
            }
            return false;
        }

        // less than
        public function lt($browser_name, $browser_version) {
            return ($this->aka($browser_name) == $this->name && $this->getIndex($browser_version, substr_count($browser_version, '.')) > $this->getIndex($this->version, substr_count($browser_version, '.')));
        }

        // less than or equal to
        public function lte($browser_name, $browser_version) {
            return ($this->aka($browser_name) == $this->name && $this->getIndex($browser_version, substr_count($browser_version, '.')) >= $this->getIndex($this->version, substr_count($browser_version, '.')));
        }

        // greater than
        public function gt($browser_name, $browser_version) {
            return ($this->aka($browser_name) == $this->name && $this->getIndex($browser_version, substr_count($browser_version, '.')) < $this->getIndex($this->version, substr_count($browser_version, '.')));
        }

        // greater than or equal to
        public function gte($browser_name, $browser_version) {
            return ($this->aka($browser_name) == $this->name && $this->getIndex($browser_version, substr_count($browser_version, '.')) <= $this->getIndex($this->version, substr_count($browser_version, '.')));
        }

        // equal to
        public function eq($browser_name, $browser_version) {
            $simple = substr($this->version, 0, strlen($browser_version));
            return ($this->aka($browser_name) == $this->name && $browser_version == $simple);
        }

        // browser, parses an expression similar to IE conditionals
        public function parse($expression = false) {
            if (!$expression) {
                return $this->name . ' ' . $this->version;
            }
            $browser = false;
            $action = false;
            $arg = false;

            // look for a browser reference in the expression
            foreach ($this->alias as $k => $v) {
                // does our expression have spaces
                if ($k == strtolower($expression)) {
                    $browser = $v;
                    $expression = str_replace($k, '', strtolower($expression));
                    break;
                } else if (preg_match('/^' . $k . '\s+/i', $expression)) {
                    $browser = $v;
                    $expression = str_replace($k, '', strtolower($expression));
                    break;
                }
            }

            $expr = explode(' ', strtolower($expression));
            if ($expr && isset($expr[0])) {
                // do our magic
                if (!$browser) {
                    $action = array_shift($expr);
                } else {
                    array_shift($expr); // 0 should always be empty in this case
                    if ($expr && $expr[0]) {
                        $action = array_shift($expr);
                        if ($expr && $expr[0]) {
                            $arg = array_shift($expr);
                        }
                    }
                }
            }

            if ($browser && $action && $arg) {
                $method = strtolower(trim($action));
                return $this->$method($browser, trim($arg));
            } else if ($browser && $action && !$arg) {
                return $this->eq($browser, trim($action));
            } else if ($action == 'version' || $action == 'name') {
                return $this->$action;
            } else if ($browser && !$action && !$arg) {
                return $this->is($browser);
            } else {
                return false;
            }
        }

    }

}